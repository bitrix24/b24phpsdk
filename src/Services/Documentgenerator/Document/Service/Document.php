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

namespace Bitrix24\SDK\Services\Documentgenerator\Document\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Documentgenerator\Document\Result\AddedDocumentResult;
use Bitrix24\SDK\Services\Documentgenerator\Document\Result\DeletedDocumentResult;
use Bitrix24\SDK\Services\Documentgenerator\Document\Result\DocumentFieldsResult;
use Bitrix24\SDK\Services\Documentgenerator\Document\Result\DocumentResult;
use Bitrix24\SDK\Services\Documentgenerator\Document\Result\DocumentsResult;
use Bitrix24\SDK\Services\Documentgenerator\Document\Result\PublicUrlResult;
use Bitrix24\SDK\Services\Documentgenerator\Document\Result\UpdatedDocumentResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['documentgenerator']))]
class Document extends AbstractService
{
    /**
     * Document constructor
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Creates a new document based on a template and data provider
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/document-generator-document-add.html
     *
     * @param int $templateId Template identifier
     * @param string $providerClassName Data provider class name (e.g. 'Bitrix\DocumentGenerator\DataProvider\Rest')
     * @param string $value External identifier of the data source (e.g. 'ORDER_1024')
     * @param array $values Field values for the document
     * @param array $fields Field configuration (providers, types, etc.)
     * @param int|null $stampsEnabled Whether to apply stamps (1 = yes, 0 = no)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.document.add',
        'https://apidocs.bitrix24.com/api-reference/document-generator/document-generator-document-add.html',
        'Creates a new document based on a template and data provider'
    )]
    public function add(
        int $templateId,
        string $providerClassName,
        string $value,
        array $values = [],
        array $fields = [],
        ?int $stampsEnabled = null
    ): AddedDocumentResult {
        $params = [
            'templateId' => $templateId,
            'providerClassName' => $providerClassName,
            'value' => $value,
        ];

        if ($values !== []) {
            $params['values'] = $values;
        }

        if ($fields !== []) {
            $params['fields'] = $fields;
        }

        if ($stampsEnabled !== null) {
            $params['stampsEnabled'] = $stampsEnabled;
        }

        return new AddedDocumentResult(
            $this->core->call(
                'documentgenerator.document.add',
                $params
            )
        );
    }

    /**
     * Updates an existing document
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/document-generator-document-update.html
     *
     * @param int $id Document identifier
     * @param array $values Field values to update
     * @param array $fields Field configuration
     * @param int|null $stampsEnabled Whether to apply stamps (1 = yes, 0 = no)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.document.update',
        'https://apidocs.bitrix24.com/api-reference/document-generator/document-generator-document-update.html',
        'Updates an existing document'
    )]
    public function update(int $id, array $values = [], array $fields = [], ?int $stampsEnabled = null): UpdatedDocumentResult
    {
        $params = [
            'id' => $id,
        ];

        if ($values !== []) {
            $params['values'] = $values;
        }

        if ($fields !== []) {
            $params['fields'] = $fields;
        }

        if ($stampsEnabled !== null) {
            $params['stampsEnabled'] = $stampsEnabled;
        }

        return new UpdatedDocumentResult(
            $this->core->call(
                'documentgenerator.document.update',
                $params
            )
        );
    }

    /**
     * Returns information about the document by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/document-generator-document-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.document.get',
        'https://apidocs.bitrix24.com/api-reference/document-generator/document-generator-document-get.html',
        'Returns information about the document by its identifier'
    )]
    public function get(int $id): DocumentResult
    {
        return new DocumentResult($this->core->call('documentgenerator.document.get', ['id' => $id]));
    }

    /**
     * Returns a list of documents
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/document-generator-document-list.html
     *
     * @param array $filter Filter parameters
     * @param array $order Order parameters
     * @param array $select Fields to select
     * @param int $start Offset for pagination
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.document.list',
        'https://apidocs.bitrix24.com/api-reference/document-generator/document-generator-document-list.html',
        'Returns a list of documents'
    )]
    public function list(array $filter = [], array $order = [], array $select = [], int $start = 0): DocumentsResult
    {
        $params = [
            'start' => $start,
        ];

        if ($filter !== []) {
            $params['filter'] = $filter;
        }

        if ($order !== []) {
            $params['order'] = $order;
        }

        if ($select !== []) {
            $params['select'] = $select;
        }

        return new DocumentsResult(
            $this->core->call(
                'documentgenerator.document.list',
                $params
            )
        );
    }

    /**
     * Deletes a document
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/document-generator-document-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.document.delete',
        'https://apidocs.bitrix24.com/api-reference/document-generator/document-generator-document-delete.html',
        'Deletes a document'
    )]
    public function delete(int $id): DeletedDocumentResult
    {
        return new DeletedDocumentResult(
            $this->core->call(
                'documentgenerator.document.delete',
                ['id' => $id]
            )
        );
    }

    /**
     * Enables or disables public URL for a document
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/document-generator-document-enable-public-url.html
     *
     * @param int $id Document identifier
     * @param int $status 1 to enable public URL, 0 to disable (default: 1)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.document.enablepublicurl',
        'https://apidocs.bitrix24.com/api-reference/document-generator/document-generator-document-enable-public-url.html',
        'Enables or disables public URL for a document'
    )]
    public function enablePublicUrl(int $id, int $status = 1): PublicUrlResult
    {
        return new PublicUrlResult(
            $this->core->call(
                'documentgenerator.document.enablepublicurl',
                [
                    'id' => $id,
                    'status' => $status,
                ]
            )
        );
    }

    /**
     * Returns the description of document fields
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/document-generator-document-get-fields.html
     *
     * @param int $id Document identifier
     * @param array $values Optional field values
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.document.getfields',
        'https://apidocs.bitrix24.com/api-reference/document-generator/document-generator-document-get-fields.html',
        'Returns the description of document fields'
    )]
    public function getFields(int $id, array $values = []): DocumentFieldsResult
    {
        $params = [
            'id' => $id,
        ];

        if ($values !== []) {
            $params['values'] = $values;
        }

        return new DocumentFieldsResult(
            $this->core->call(
                'documentgenerator.document.getfields',
                $params
            )
        );
    }

    /**
     * Count documents
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function count(): int
    {
        return $this->list()->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
