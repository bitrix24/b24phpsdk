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

namespace Bitrix24\SDK\Services\Catalog\Document\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Document\Result\DocumentFieldsResult;
use Bitrix24\SDK\Services\Catalog\Document\Result\DocumentModeStatusResult;
use Bitrix24\SDK\Services\Catalog\Document\Result\DocumentResult;
use Bitrix24\SDK\Services\Catalog\Document\Result\DocumentsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Document extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new warehouse accounting document
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-add.html',
        'Adds a new warehouse accounting document'
    )]
    public function add(array $fields): DocumentResult
    {
        return new DocumentResult($this->core->call('catalog.document.add', ['fields' => $fields]));
    }

    /**
     * Updates a warehouse accounting document by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-update.html',
        'Updates a warehouse accounting document by its identifier'
    )]
    public function update(int $id, array $fields): DocumentResult
    {
        return new DocumentResult($this->core->call('catalog.document.update', ['id' => $id, 'fields' => $fields]));
    }

    /**
     * Returns a list of warehouse accounting documents by filter
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-list.html',
        'Returns a list of warehouse accounting documents by filter'
    )]
    public function list(array $select = [], array $filter = []): DocumentsResult
    {
        return new DocumentsResult(
            $this->core->call(
                'catalog.document.list',
                ['select' => $select, 'filter' => $filter]
            )
        );
    }

    /**
     * Deletes a warehouse accounting document by identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-delete.html',
        'Deletes a warehouse accounting document by identifier'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.document.delete', ['id' => $id]));
    }

    /**
     * Deletes a group of warehouse accounting documents by identifiers
     *
     * @param int[] $documentIds
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-delete-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.deleteList',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-delete-list.html',
        'Deletes a group of warehouse accounting documents by identifiers'
    )]
    public function deleteList(array $documentIds): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.document.deleteList', ['documentIds' => $documentIds]));
    }

    /**
     * Conducts a warehouse accounting document, updating stock balances
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-conduct.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.conduct',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-conduct.html',
        'Conducts a warehouse accounting document, updating stock balances'
    )]
    public function conduct(int $id): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.document.conduct', ['id' => $id]));
    }

    /**
     * Conducts a group of warehouse accounting documents, updating stock balances
     *
     * @param int[] $documentIds
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-conduct-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.conductList',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-conduct-list.html',
        'Conducts a group of warehouse accounting documents, updating stock balances'
    )]
    public function conductList(array $documentIds): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.document.conductList', ['documentIds' => $documentIds]));
    }

    /**
     * Cancels conducting of a warehouse accounting document, updating stock balances
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-cancel.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.cancel',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-cancel.html',
        'Cancels conducting of a warehouse accounting document, updating stock balances'
    )]
    public function cancel(int $id): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.document.cancel', ['id' => $id]));
    }

    /**
     * Cancels conducting of a group of warehouse accounting documents, updating stock balances
     *
     * @param int[] $documentIds
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-cancel-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.cancelList',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-cancel-list.html',
        'Cancels conducting of a group of warehouse accounting documents, updating stock balances'
    )]
    public function cancelList(array $documentIds): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.document.cancelList', ['documentIds' => $documentIds]));
    }

    /**
     * Returns the fields of a warehouse accounting document
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-get-fields.html',
        'Returns the fields of a warehouse accounting document'
    )]
    public function getFields(): DocumentFieldsResult
    {
        return new DocumentFieldsResult($this->core->call('catalog.document.getFields'));
    }

    /**
     * Checks whether warehouse accounting mode is enabled
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-mode-status.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.document.mode.status',
        'https://apidocs.bitrix24.com/api-reference/catalog/document/catalog-document-mode-status.html',
        'Checks whether warehouse accounting mode is enabled'
    )]
    public function modeStatus(): DocumentModeStatusResult
    {
        return new DocumentModeStatusResult($this->core->call('catalog.document.mode.status'));
    }
}
