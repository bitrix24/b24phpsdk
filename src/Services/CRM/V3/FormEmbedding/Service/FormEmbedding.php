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

namespace Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Exceptions\VersionMismatchException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Result\FormEmbeddingResult;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Result\FormEmbeddingVariantResult;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Result\UpdatedFormEmbeddingResult;
use Carbon\CarbonImmutable;

/**
 * Website placement of CRM forms: embed codes, the public link and click/auto display settings.
 */
#[ApiServiceMetadata(new Scope(['crm']))]
class FormEmbedding extends AbstractService
{
    /**
     * Returns the public link and all placement variants (inline, click, auto) with their settings.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/placement/crm-form-embedding-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.embedding.get',
        'https://apidocs.bitrix24.com/api-reference/crm/form/placement/crm-form-embedding-get.html',
        'Returns the placement codes and public link of a CRM form',
        ApiVersion::v3
    )]
    public function get(int $formId): FormEmbeddingResult
    {
        return new FormEmbeddingResult($this->core->call('crm.form.embedding.get', ['id' => $formId], ApiVersion::v3));
    }

    /**
     * Returns the embed code (or the public URL for the link variant) of one placement variant.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/placement/crm-form-embedding-get.html
     *
     * @param string|null              $buttonText button or link caption, click variant only
     * @param EmbeddingButtonType|null $buttonType click variant only
     * @param positive-int|null        $delay      auto display delay in seconds, auto variant only
     * @throws InvalidArgumentException when an option does not belong to the variant
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.embedding.get',
        'https://apidocs.bitrix24.com/api-reference/crm/form/placement/crm-form-embedding-get.html',
        'Returns the embed code of one placement variant of a CRM form',
        ApiVersion::v3
    )]
    public function getVariant(
        int $formId,
        EmbeddingVariant $variant,
        ?string $buttonText = null,
        ?EmbeddingButtonType $buttonType = null,
        ?int $delay = null,
    ): FormEmbeddingVariantResult {
        // the portal rejects these options for other variants; fail before the request
        if (($buttonText !== null || $buttonType instanceof EmbeddingButtonType) && $variant !== EmbeddingVariant::click) {
            throw new InvalidArgumentException('buttonText and buttonType are available only for the click embedding variant');
        }

        if ($delay !== null && $variant !== EmbeddingVariant::auto) {
            throw new InvalidArgumentException('delay is available only for the auto embedding variant');
        }

        return new FormEmbeddingVariantResult(
            $this->core->call(
                'crm.form.embedding.get',
                array_filter(
                    [
                        'id'         => $formId,
                        'variant'    => $variant->value,
                        'buttonText' => $buttonText,
                        'buttonType' => $buttonType?->value,
                        'delay'      => $delay,
                    ],
                    static fn (mixed $value): bool => $value !== null
                ),
                ApiVersion::v3
            )
        );
    }

    /**
     * Saves click and/or auto display settings; omitted settings stay unchanged, a null setting value resets it.
     * The change bumps the form version (updatedTime).
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/placement/crm-form-embedding-update.html
     *
     * @param CarbonImmutable           $updatedTime  form version read from Form::get()
     * @param array<string, mixed>|null $clickSettings e.g. ['type' => 'popup', 'position' => 'left', 'button' => ['use' => true]]
     * @param array<string, mixed>|null $autoSettings  e.g. ['type' => 'panel', 'delay' => 10]
     * @throws VersionMismatchException when the form changed after $updatedTime
     * @throws InvalidArgumentException when no settings are passed
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.embedding.update',
        'https://apidocs.bitrix24.com/api-reference/crm/form/placement/crm-form-embedding-update.html',
        'Saves click and auto display settings of a CRM form',
        ApiVersion::v3
    )]
    public function update(
        int $formId,
        CarbonImmutable $updatedTime,
        ?array $clickSettings = null,
        ?array $autoSettings = null,
    ): UpdatedFormEmbeddingResult {
        $fields = array_filter(
            [
                'click' => $clickSettings === null ? null : ['settings' => $clickSettings],
                'auto'  => $autoSettings === null ? null : ['settings' => $autoSettings],
            ],
            static fn (?array $value): bool => $value !== null
        );
        if ($fields === []) {
            throw new InvalidArgumentException('pass click and/or auto settings to update');
        }

        return new UpdatedFormEmbeddingResult(
            $this->core->call(
                'crm.form.embedding.update',
                ['id' => $formId, 'updatedTime' => $updatedTime->toIso8601String(), 'fields' => $fields],
                ApiVersion::v3
            )
        );
    }
}
