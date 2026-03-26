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

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result\AddedDocumentResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result\DeletedDocumentResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result\DocumentResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result\DocumentsResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result\PublicUrlResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result\UpdatedDocumentResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
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
     * Creates a new document based on a template and CRM entity
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-add.html
     *
     * @param int $templateId Template identifier
     * @param int $entityTypeId CRM entity type identifier
     * @param int $entityId CRM entity identifier
     * @param array $values Additional field values
     * @param int|null $stampsEnabled Whether to apply stamps (1 = yes, 0 = no)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.document.add',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-add.html',
        'Creates a new document based on a template and CRM entity'
    )]
    public function add(
        int $templateId,
        int $entityTypeId,
        int $entityId,
        array $values = [],
        ?int $stampsEnabled = null
    ): AddedDocumentResult {
        $params = [
            'templateId' => $templateId,
            'entityTypeId' => $entityTypeId,
            'entityId' => $entityId,
        ];

        if ($values !== []) {
            $params['values'] = $values;
        }

        if ($stampsEnabled !== null) {
            $params['stampsEnabled'] = $stampsEnabled;
        }

        return new AddedDocumentResult(
            $this->core->call(
                'crm.documentgenerator.document.add',
                $params
            )
        );
    }

    /**
     * Deletes a document
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.document.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-delete.html',
        'Deletes a document'
    )]
    public function delete(int $id): DeletedDocumentResult
    {
        $params = [
            'id' => $id,
        ];

        return new DeletedDocumentResult(
            $this->core->call(
                'crm.documentgenerator.document.delete',
                $params
            )
        );
    }

    /**
     * Returns information about the document by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.document.get',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-get.html',
        'Returns information about the document by its identifier'
    )]
    public function get(int $id): DocumentResult
    {
        return new DocumentResult($this->core->call('crm.documentgenerator.document.get', ['id' => $id]));
    }

    /**
     * Returns a list of documents
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-list.html
     *
     * @param array $filter Filter parameters
     * @param array $order Order parameters
     * @param int $start Offset for pagination
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.document.list',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-list.html',
        'Returns a list of documents'
    )]
    public function list(array $filter = [], array $order = [], int $start = 0): DocumentsResult
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

        return new DocumentsResult(
            $this->core->call(
                'crm.documentgenerator.document.list',
                $params
            )
        );
    }

    /**
     * Updates an existing document
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-update.html
     *
     * @param int $id Document identifier
     * @param array $values Field values to update
     * @param int|null $stampsEnabled Whether to apply stamps (1 = yes, 0 = no)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.document.update',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-update.html',
        'Updates an existing document'
    )]
    public function update(int $id, array $values = [], ?int $stampsEnabled = null): UpdatedDocumentResult
    {
        $params = [
            'id' => $id,
        ];

        if ($values !== []) {
            $params['values'] = $values;
        }

        if ($stampsEnabled !== null) {
            $params['stampsEnabled'] = $stampsEnabled;
        }

        return new UpdatedDocumentResult(
            $this->core->call(
                'crm.documentgenerator.document.update',
                $params
            )
        );
    }

    /**
     * Returns the description of document fields
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.document.getfields',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-get-fields.html',
        'Returns the description of document fields'
    )]
    public function getFields(): FieldsResult
    {
        return new FieldsResult(
            $this->core->call(
                'crm.documentgenerator.document.getfields',
                []
            )
        );
    }

    /**
     * Enables public URL for a document
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-enable-public-url.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.document.enablepublicurl',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-enable-public-url.html',
        'Enables public URL for a document'
    )]
    public function enablePublicUrl(int $id): PublicUrlResult
    {
        return new PublicUrlResult(
            $this->core->call(
                'crm.documentgenerator.document.enablepublicurl',
                ['id' => $id]
            )
        );
    }

    /**
     * Uploads a file for a document
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-upload.html
     *
     * @param int $id Document identifier
     * @param string $fileContent File content in Base64 format
     * @param string $fileName File name with extension
     * @param int $entityTypeId CRM entity type identifier
     * @param int $entityId CRM entity identifier
     * @param string $title Document title
     * @param string $number Document number
     * @param string $region Region code (e.g. 'uk', 'us', 'de')
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.document.upload',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/crm-document-generator-document-upload.html',
        'Uploads a file for a document'
    )]
    public function upload(
        int $id,
        string $fileContent,
        string $fileName,
        int $entityTypeId,
        int $entityId,
        string $title,
        string $number,
        string $region = 'uk'
    ): DocumentResult {
        return new DocumentResult(
            $this->core->call(
                'crm.documentgenerator.document.upload',
                [
                    'id' => $id,
                    'fields' => [
                        'fileContent' => $fileContent,
                        'fileName' => $fileName,
                        'entityTypeId' => $entityTypeId,
                        'entityId' => $entityId,
                        'title' => $title,
                        'number' => $number,
                        'region' => $region,
                    ],
                ]
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
