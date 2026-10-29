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

namespace Bitrix24\SDK\Services\Catalog\V3\Section\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\ItemBuilderInterface;
use Bitrix24\SDK\Core\Contracts\SelectBuilderInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Filters\FilterBuilderInterface;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\V3\Section\Result\DeletedSectionResult;
use Bitrix24\SDK\Services\Catalog\V3\Section\Result\SectionResult;
use Bitrix24\SDK\Services\Catalog\V3\Section\Result\SectionsResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Section extends AbstractService
{
    /**
     * Returns a product section by ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-get-rest-v3.html
     *
     * @param array<int, string>|SectionSelectBuilder $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.section.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-get-rest-v3.html',
        'Returns a product section by ID',
        ApiVersion::v3
    )]
    public function get(int $id, array|SectionSelectBuilder $select = []): SectionResult
    {
        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new SectionResult(
            $this->core->call('catalog.section.get', $this->withoutEmpty(['id' => $id, 'select' => $select]), ApiVersion::v3)
        );
    }

    /**
     * Returns product sections of a catalog information block.
     *
     * The API requires a filter by information block, so `$iblockId` is mandatory and is prepended to `$filter`.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-list-rest-v3.html
     *
     * @param array<int, mixed>|FilterBuilderInterface $filter Additional conditions such as [['iblockSectionId', '=', 0]]
     * @param array<int, string>|SectionSelectBuilder  $select
     * @param array<string, string>                    $order  Sort order, e.g. ['sort' => 'ASC']
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.section.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-list-rest-v3.html',
        'Returns product sections of a catalog information block',
        ApiVersion::v3
    )]
    public function list(
        int $iblockId,
        array|FilterBuilderInterface $filter = [],
        array|SectionSelectBuilder $select = [],
        array $order = [],
        ?SectionListPagination $pagination = null,
    ): SectionsResult {
        if ($filter instanceof FilterBuilderInterface) {
            $filter = $filter->toArray();
        }

        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new SectionsResult(
            $this->core->call(
                'catalog.section.list',
                $this->withoutEmpty([
                    'filter'     => [['iblockId', '=', $iblockId], ...$filter],
                    'select'     => $select,
                    'order'      => $order,
                    'pagination' => $pagination?->toArray(),
                ]),
                ApiVersion::v3
            )
        );
    }

    /**
     * Adds a product section and returns the created record.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-add-rest-v3.html
     *
     * @param array<string, mixed>|SectionItemBuilder $fields iblockId and name are required
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.section.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-add-rest-v3.html',
        'Adds a product section',
        ApiVersion::v3
    )]
    public function add(array|SectionItemBuilder $fields): SectionResult
    {
        if ($fields instanceof ItemBuilderInterface) {
            $fields = $fields->build();
        }

        return new SectionResult($this->core->call('catalog.section.add', ['fields' => $fields], ApiVersion::v3));
    }

    /**
     * Updates a product section and returns the updated record; iblockId cannot be changed.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-update-rest-v3.html
     *
     * @param array<string, mixed>|SectionItemBuilder $fields
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.section.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-update-rest-v3.html',
        'Updates a product section',
        ApiVersion::v3
    )]
    public function update(int $id, array|SectionItemBuilder $fields): SectionResult
    {
        if ($fields instanceof ItemBuilderInterface) {
            $fields = $fields->build();
        }

        return new SectionResult(
            $this->core->call('catalog.section.update', ['id' => $id, 'fields' => $fields], ApiVersion::v3)
        );
    }

    /**
     * Deletes a product section together with its subsections.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-delete-rest-v3.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.section.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-delete-rest-v3.html',
        'Deletes a product section with its subsections',
        ApiVersion::v3
    )]
    public function delete(int $id): DeletedSectionResult
    {
        return new DeletedSectionResult($this->core->call('catalog.section.delete', ['id' => $id], ApiVersion::v3));
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    private function withoutEmpty(array $parameters): array
    {
        return array_filter($parameters, static fn (mixed $value): bool => $value !== null && $value !== []);
    }
}
