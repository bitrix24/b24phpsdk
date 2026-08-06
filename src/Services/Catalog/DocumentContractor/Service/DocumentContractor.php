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

namespace Bitrix24\SDK\Services\Catalog\DocumentContractor\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Result\DocumentContractorFieldsResult;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Result\DocumentContractorResult;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Result\DocumentContractorsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class DocumentContractor extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Binds a vendor, contact or company, to a warehouse accounting document of type "Receipt"
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/catalog-documentcontractor-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.documentcontractor.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/catalog-documentcontractor-add.html',
        'Binds a vendor, contact or company, to a warehouse accounting document of type "Receipt"'
    )]
    public function add(array $fields): DocumentContractorResult
    {
        return new DocumentContractorResult($this->core->call('catalog.documentcontractor.add', ['fields' => $fields]));
    }

    /**
     * Returns a list of vendor bindings to inventory accounting documents by filter
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/catalog-documentcontractor-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.documentcontractor.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/catalog-documentcontractor-list.html',
        'Returns a list of vendor bindings to inventory accounting documents by filter'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], int $start = 0): DocumentContractorsResult
    {
        return new DocumentContractorsResult(
            $this->core->call(
                'catalog.documentcontractor.list',
                ['select' => $select, 'filter' => $filter, 'order' => $order, 'start' => $start]
            )
        );
    }

    /**
     * Removes the vendor binding from the inventory accounting document by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/catalog-documentcontractor-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.documentcontractor.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/catalog-documentcontractor-delete.html',
        'Removes the vendor binding from the inventory accounting document by its identifier'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.documentcontractor.delete', ['id' => $id]));
    }

    /**
     * Returns the description of fields for binding a vendor, contact or company, to a warehouse accounting document
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/catalog-documentcontractor-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.documentcontractor.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/documentcontractor/catalog-documentcontractor-get-fields.html',
        'Returns the description of fields for binding a vendor, contact or company, to a warehouse accounting document'
    )]
    public function getFields(): DocumentContractorFieldsResult
    {
        return new DocumentContractorFieldsResult($this->core->call('catalog.documentcontractor.getFields'));
    }
}
