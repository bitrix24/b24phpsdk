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

namespace Bitrix24\SDK\Services\Catalog\DocumentElement\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Result\DocumentElementFieldsResult;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Result\DocumentElementResult;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Result\DocumentElementsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class DocumentElement extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a product line item to a warehouse accounting document
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/catalog-document-element-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.element.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/catalog-document-element-add.html',
        'Adds a product line item to a warehouse accounting document'
    )]
    public function add(array $fields): DocumentElementResult
    {
        return new DocumentElementResult($this->core->call('catalog.document.element.add', ['fields' => $fields]));
    }

    /**
     * Updates a warehouse accounting document line item by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/catalog-document-element-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.element.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/catalog-document-element-update.html',
        'Updates a warehouse accounting document line item by its identifier'
    )]
    public function update(int $id, array $fields): DocumentElementResult
    {
        return new DocumentElementResult(
            $this->core->call('catalog.document.element.update', ['id' => $id, 'fields' => $fields])
        );
    }

    /**
     * Returns a list of warehouse accounting document line items by filter
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/catalog-document-element-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.element.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/catalog-document-element-list.html',
        'Returns a list of warehouse accounting document line items by filter'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): DocumentElementsResult
    {
        return new DocumentElementsResult(
            $this->core->call(
                'catalog.document.element.list',
                ['select' => $select, 'filter' => $filter, 'order' => $order]
            )
        );
    }

    /**
     * Deletes a warehouse accounting document line item by identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/catalog-document-element-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.element.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/catalog-document-element-delete.html',
        'Deletes a warehouse accounting document line item by identifier'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.document.element.delete', ['id' => $id]));
    }

    /**
     * Returns the fields of a warehouse accounting document line item
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/catalog-document-element-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.element.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/document-element/catalog-document-element-get-fields.html',
        'Returns the fields of a warehouse accounting document line item'
    )]
    public function getFields(): DocumentElementFieldsResult
    {
        return new DocumentElementFieldsResult($this->core->call('catalog.document.element.getFields'));
    }
}
