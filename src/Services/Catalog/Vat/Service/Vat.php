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

namespace Bitrix24\SDK\Services\Catalog\Vat\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Vat\Result\VatFieldsResult;
use Bitrix24\SDK\Services\Catalog\Vat\Result\VatResult;
use Bitrix24\SDK\Services\Catalog\Vat\Result\VatsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Vat extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new VAT rate
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.vat.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-add.html',
        'Adds a new VAT rate'
    )]
    public function add(array $fields): VatResult
    {
        return new VatResult($this->core->call('catalog.vat.add', ['fields' => $fields]));
    }

    /**
     * Updates a VAT rate by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.vat.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-update.html',
        'Updates a VAT rate by its identifier'
    )]
    public function update(int $id, array $fields): VatResult
    {
        return new VatResult($this->core->call('catalog.vat.update', ['id' => $id, 'fields' => $fields]));
    }

    /**
     * Returns VAT rate information by identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.vat.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-get.html',
        'Returns VAT rate information by identifier'
    )]
    public function get(int $id): VatResult
    {
        return new VatResult($this->core->call('catalog.vat.get', ['id' => $id]));
    }

    /**
     * Returns a list of VAT rates by filter
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.vat.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-list.html',
        'Returns a list of VAT rates by filter'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): VatsResult
    {
        return new VatsResult(
            $this->core->call(
                'catalog.vat.list',
                ['select' => $select, 'filter' => $filter, 'order' => $order]
            )
        );
    }

    /**
     * Deletes a VAT rate by identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.vat.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-delete.html',
        'Deletes a VAT rate by identifier'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.vat.delete', ['id' => $id]));
    }

    /**
     * Returns the fields of a VAT rate
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.vat.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-get-fields.html',
        'Returns the fields of a VAT rate'
    )]
    public function getFields(): VatFieldsResult
    {
        return new VatFieldsResult($this->core->call('catalog.vat.getFields'));
    }
}
