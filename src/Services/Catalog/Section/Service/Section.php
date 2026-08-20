<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Section\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Section\Result\SectionFieldsResult;
use Bitrix24\SDK\Services\Catalog\Section\Result\SectionResult;
use Bitrix24\SDK\Services\Catalog\Section\Result\SectionsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Section extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new trade-catalog section
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.section.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-add.html',
        'Adds a new trade-catalog section'
    )]
    public function add(array $fields): SectionResult
    {
        return new SectionResult($this->core->call('catalog.section.add', ['fields' => $fields]));
    }

    /**
     * Updates a trade-catalog section by its identifier
     *
     * Note: despite the API documentation listing `iblockId` as optional on update, the live
     * API rejects the call with "Required fields: iblockId" if it is omitted from $fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.section.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-update.html',
        'Updates a trade-catalog section by its identifier'
    )]
    public function update(int $id, array $fields): SectionResult
    {
        return new SectionResult($this->core->call('catalog.section.update', ['id' => $id, 'fields' => $fields]));
    }

    /**
     * Returns a trade-catalog section by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.section.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-get.html',
        'Returns a trade-catalog section by its identifier'
    )]
    public function get(int $id): SectionResult
    {
        return new SectionResult($this->core->call('catalog.section.get', ['id' => $id]));
    }

    /**
     * Returns a list of trade-catalog sections by filter
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.section.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-list.html',
        'Returns a list of trade-catalog sections by filter'
    )]
    public function list(array $select = [], array $filter = []): SectionsResult
    {
        return new SectionsResult(
            $this->core->call(
                'catalog.section.list',
                ['select' => $select, 'filter' => $filter]
            )
        );
    }

    /**
     * Deletes a trade-catalog section by identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.section.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-delete.html',
        'Deletes a trade-catalog section by identifier'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.section.delete', ['id' => $id]));
    }

    /**
     * Returns the fields of a trade-catalog section
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.section.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-get-fields.html',
        'Returns the description of trade-catalog section fields'
    )]
    public function getFields(): SectionFieldsResult
    {
        return new SectionFieldsResult($this->core->call('catalog.section.getFields'));
    }
}
