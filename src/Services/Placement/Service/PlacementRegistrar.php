<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Placement\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Placement\Common\PlacementHandlerMetadata;
use Bitrix24\SDK\Services\Placement\PlacementConflictPolicy;
use Bitrix24\SDK\Services\Placement\PlacementRegistrationStatus;
use Bitrix24\SDK\Services\Placement\Result\PlacementRegistrationItemResult;
use Bitrix24\SDK\Services\Placement\Result\PlacementRegistrationResult;

/**
 * Reconciles declared handlers without pruning unrelated registrations.
 * Replacement is sequential and best effort; concurrent installers require external coordination.
 */
final readonly class PlacementRegistrar
{
    public function __construct(private Placement $placement)
    {
    }

    /**
     * @param list<PlacementHandlerMetadata> $declarations
     * @throws BaseException Initial inventory failures propagate before any writes.
     */
    public function register(array $declarations, PlacementConflictPolicy $conflictPolicy = PlacementConflictPolicy::fail): PlacementRegistrationResult
    {
        $identities = [];
        foreach ($declarations as $declaration) {
            if (!$declaration instanceof PlacementHandlerMetadata) {
                throw new InvalidArgumentException('Every placement declaration must be PlacementHandlerMetadata.');
            }

            $identity = $this->identity($declaration->placementCode, $declaration->handlerUrl->getUrl(), $declaration->b24UserId ?? 0);
            if (isset($identities[$identity])) {
                throw new InvalidArgumentException('Duplicate placement declaration identity.');
            }

            // Validate every language map before any API calls.
            $this->normalizeLanguage($declaration->lang);
            $identities[$identity] = true;
        }

        if ($declarations === []) {
            return new PlacementRegistrationResult([]);
        }

        $rows = $this->placement->get()->getCoreResponse()->getResponseData()->getResult();
        [$index, $uncertain] = $this->indexRows($rows);
        $items = [];
        foreach ($declarations as $declaration) {
            $identity = $this->identity($declaration->placementCode, $declaration->handlerUrl->getUrl(), $declaration->b24UserId ?? 0);
            $baseIdentity = $this->baseIdentity($declaration->placementCode, $declaration->handlerUrl->getUrl());
            try {
                if (isset($uncertain['*']) || isset($uncertain[$baseIdentity])) {
                    throw new BaseException('Existing placement identity cannot be determined safely.');
                }

                $matches = $index[$identity] ?? [];
                if (count($matches) > 1) {
                    throw new BaseException('Multiple existing registrations have the same placement identity.');
                }

                if ($matches === []) {
                    $this->bind($declaration);
                    $items[] = new PlacementRegistrationItemResult($declaration, PlacementRegistrationStatus::created);
                    continue;
                }

                $previous = $matches[0];
                $this->assertObservableConfiguration($previous);
                if ($this->canonicalize($previous['options']) === $this->canonicalize($declaration->options)
                    && $this->canonicalize($this->normalizeLanguage($previous['langAll'])) === $this->canonicalize($this->normalizeLanguage($declaration->lang))) {
                    $items[] = new PlacementRegistrationItemResult($declaration, PlacementRegistrationStatus::skipped);
                    continue;
                }

                if ($conflictPolicy === PlacementConflictPolicy::fail) {
                    throw new BaseException('Existing placement configuration conflicts with the declaration.');
                }

                if (!array_key_exists('userId', $previous)) {
                    throw new BaseException('Existing placement user identity is not observable for restoration.');
                }

                $items[] = $this->replace($declaration, $previous);
            } catch (BaseException $error) {
                $items[] = new PlacementRegistrationItemResult($declaration, PlacementRegistrationStatus::failed, $error);
            }
        }

        return new PlacementRegistrationResult($items);
    }

    private function bind(PlacementHandlerMetadata $declaration): void
    {
        if (!$this->placement->bind($declaration->placementCode, $declaration->handlerUrl, $this->normalizeLanguage($declaration->lang), $declaration->options, $declaration->b24UserId)->isSuccess()) {
            throw new BaseException('placement.bind returned false.');
        }
    }

    private function replace(PlacementHandlerMetadata $declaration, array $previous): PlacementRegistrationItemResult
    {
        $userId = (int) ($previous['userId'] ?? 0);
        $count = $this->placement->unbind($declaration->placementCode, $declaration->handlerUrl, $userId)->getDeletedPlacementHandlersCount();
        if ($count !== 1) {
            throw new BaseException(sprintf('Expected one removed placement handler, received %d.', $count));
        }

        try {
            $this->bind($declaration);
            return new PlacementRegistrationItemResult($declaration, PlacementRegistrationStatus::updated);
        } catch (BaseException $error) {
            try {
                if (!$this->placement->bind($previous['placement'], $previous['handler'], $previous['langAll'], $previous['options'], $userId === 0 ? null : $userId)->isSuccess()) {
                    throw new BaseException('Restoring previous placement configuration returned false.', $error->getCode(), $error);
                }

                return new PlacementRegistrationItemResult($declaration, PlacementRegistrationStatus::failed, $error, previousConfigurationRestored: true);
            } catch (BaseException $rollbackError) {
                return new PlacementRegistrationItemResult($declaration, PlacementRegistrationStatus::failed, $error, $rollbackError, false);
            }
        }
    }

    /** @return array{array<string, list<array>>, array<string, true>} */
    private function indexRows(array $rows): array
    {
        $index = [];
        $uncertain = [];
        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row['placement'], $row['handler']) || !is_string($row['placement']) || !is_string($row['handler']) || trim($row['placement']) === '' || $row['handler'] === '') {
                $uncertain['*'] = true;
                continue;
            }

            $userId = $row['userId'] ?? 0;
            if ((!is_int($userId) && !is_string($userId)) || filter_var($userId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
                $uncertain[$this->baseIdentity($row['placement'], $row['handler'])] = true;
                continue;
            }

            $index[$this->identity($row['placement'], $row['handler'], (int) $userId)][] = $row;
        }

        return [$index, $uncertain];
    }

    private function assertObservableConfiguration(array $row): void
    {
        if (!isset($row['options'], $row['langAll']) || !is_array($row['options']) || !is_array($row['langAll'])) {
            throw new BaseException('Existing placement OPTIONS and LANG_ALL must be observable arrays.');
        }

        if ($row['langAll'] === [] && (($row['title'] ?? '') !== '' || ($row['description'] ?? '') !== '')) {
            throw new BaseException('Existing legacy title or description cannot be restored through LANG_ALL.');
        }

        $this->normalizeLanguage($row['langAll']);
    }

    /** Returns the unambiguous language payload sent by new registrations. */
    private function normalizeLanguage(array $lang): array
    {
        $normalized = [];
        foreach ($lang as $locale => $fields) {
            if (!is_string($locale) || !is_array($fields)) {
                throw new InvalidArgumentException('Placement language entries must map locale strings to field arrays.');
            }

            $locale = strtolower($locale);
            if (array_key_exists($locale, $normalized)) {
                throw new InvalidArgumentException('Placement language entries contain duplicate normalized locales.');
            }

            $normalizedFields = [];
            foreach ($fields as $field => $value) {
                if (!is_string($field)) {
                    throw new InvalidArgumentException('Placement language field names must be strings.');
                }

                $field = strtoupper($field);
                if (array_key_exists($field, $normalizedFields)) {
                    throw new InvalidArgumentException('Placement language entries contain duplicate normalized field names.');
                }

                $normalizedFields[$field] = $value;
            }

            if (!isset($normalizedFields['TITLE']) || !is_string($normalizedFields['TITLE'])) {
                throw new InvalidArgumentException('Placement language entries require a string TITLE.');
            }

            foreach (['DESCRIPTION', 'GROUP_NAME'] as $field) {
                if (array_key_exists($field, $normalizedFields) && !is_string($normalizedFields[$field])) {
                    throw new InvalidArgumentException(sprintf('Placement language field %s must be a string.', $field));
                }
            }

            $normalized[$locale] = $normalizedFields + ['DESCRIPTION' => '', 'GROUP_NAME' => ''];
        }

        return $normalized;
    }

    /** Builds comparison data only; tagged structures must never be sent to the API. */
    private function canonicalize(array $value): array
    {
        $isList = array_is_list($value);
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->canonicalize($item);
            }
        }

        if (!$isList) {
            ksort($value, SORT_STRING);
        }

        return ['type' => $isList ? 'list' : 'map', 'value' => $value];
    }

    private function identity(string $code, string $url, int $userId): string
    {
        return serialize([strtoupper($code), $url, $userId]);
    }

    private function baseIdentity(string $code, string $url): string
    {
        return serialize([strtoupper($code), $url]);
    }
}
