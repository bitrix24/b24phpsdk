<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <titarx@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Template;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle parameter naming differences in crm.documentgenerator.template.* REST methods:
 * - delete uses 'id' instead of 'ID'
 * - update uses 'id' instead of 'ID'
 * - list results are wrapped in 'templates' key and use lowercase 'id'
 *
 * @package Bitrix24\SDK\Services\CRM\Documentgenerator\Template
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Determines the ID key — lowercase 'id' for template generator
     */
    #[\Override]
    protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string
    {
        return 'id';
    }

    /**
     * Extracts elements from batch result, unwrapping the 'templates' key
     */
    #[\Override]
    protected function extractElementsFromBatchResult(ResponseData $responseData, bool $isCrmItemsInBatch): array
    {
        $resultData = $responseData->getResult();

        if (array_key_exists('templates', $resultData) && is_array($resultData['templates'])) {
            return $resultData['templates'];
        }

        return $resultData;
    }

    /**
     * Returns reference field path including 'templates' wrapper for batch query chaining
     */
    #[\Override]
    protected function getReferenceFieldPath(string $prevCommandId, int $lastIndex, string $keyId, bool $isCrmItemsInBatch): string
    {
        return sprintf('$result[%s][templates][%d][%s]', $prevCommandId, $lastIndex, $keyId);
    }

    /**
     * Get traversable list using lowercase 'id' key and 'templates' result wrapper
     *
     * Delegates to parent implementation which uses overridden helper methods:
     * - determineKeyId() returns 'id' instead of 'ID'
     * - extractElementsFromBatchResult() unwraps 'templates' key
     * - getReferenceFieldPath() includes 'templates' in batch reference path
     *
     * @param array<string,string> $order
     * @param array<string,mixed> $filter
     * @param array<string,mixed> $select
     *
     * @return \Generator<mixed>
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    #[\Override]
    public function getTraversableList(
        string $apiMethod,
        ?array $order = [],
        ?array $filter = [],
        ?array $select = [],
        ?int $limit = null,
        ?array $additionalParameters = null
    ): Generator {
        yield from parent::getTraversableList($apiMethod, $order, $filter, $select, $limit, $additionalParameters);
    }

    /**
     * Update entity items with batch call
     *
     * The crm.documentgenerator.template.update method uses 'id' (lowercase)
     * instead of the standard 'ID' key used by most other REST methods.
     *
     * Update elements in array with structure:
     * element_id => [
     *   'fields' => [] // required: template fields to update
     * ]
     *
     * @param array<int, array<string, mixed>> $entityItems
     *
     * @return Generator<int, ResponseData>|ResponseData[]
     * @throws BaseException
     */
    #[\Override]
    public function updateEntityItems(string $apiMethod, array $entityItems): Generator
    {
        $this->logger->debug(
            'updateEntityItems.start',
            [
                'apiMethod' => $apiMethod,
                'entityItems' => $entityItems,
            ]
        );

        try {
            $this->clearCommands();

            foreach ($entityItems as $entityItemId => $entityItem) {
                if (!is_int($entityItemId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of template id «%s», the id must be integer type',
                            gettype($entityItemId),
                            $entityItemId
                        )
                    );
                }

                if (!array_key_exists('fields', $entityItem)) {
                    throw new InvalidArgumentException(
                        sprintf('array key «fields» not found in entity item with id %s', $entityItemId)
                    );
                }

                $cmdArguments = [
                    'id' => $entityItemId,
                    'fields' => $entityItem['fields'],
                ];

                $this->registerCommand($apiMethod, $cmdArguments);
            }

            foreach ($this->getTraversable(true) as $cnt => $updatedItemResult) {
                yield $cnt => $updatedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch update entity items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch update entity items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );

            throw new BaseException($errorMessage, $exception->getCode(), $exception);
        }

        $this->logger->debug('updateEntityItems.finish');
    }

    /**
     * Delete entity items with batch call
     *
     *
     * @return Generator<int, ResponseData>|ResponseData[]
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\Override]
    public function deleteEntityItems(
        string $apiMethod,
        array $entityItemId,
        ?array $additionalParameters = null
    ): Generator {
        $this->logger->debug(
            'deleteEntityItems.start',
            [
                'apiMethod' => $apiMethod,
                'entityItems' => $entityItemId,
                'additionalParameters' => $additionalParameters,
            ]
        );

        try {
            $this->clearCommands();
            foreach ($entityItemId as $cnt => $code) {
                if (!is_int($code)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of template id «%s» at position %s, id must be integer type',
                            gettype($code),
                            $code,
                            $cnt
                        )
                    );
                }

                $parameters = ['id' => $code];
                $this->registerCommand($apiMethod, $parameters);
            }

            foreach ($this->getTraversable(true) as $cnt => $deletedItemResult) {
                yield $cnt => $deletedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch delete entity items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch delete entity items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );

            throw new BaseException($errorMessage, $exception->getCode(), $exception);
        }

        $this->logger->debug('deleteEntityItems.finish');
    }
}
