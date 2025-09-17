<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Delivery\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\Delivery\Result\DeliveriesResult;
use Bitrix24\SDK\Services\Sale\Delivery\Result\DeliveryAddResult;
use Bitrix24\SDK\Services\Sale\Delivery\Result\DeliveryConfigGetResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class Delivery extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a delivery service.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-add.html
     *
     * @param array $fields Field values for creating a delivery service
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.add',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-add.html',
        'Adds a delivery service.'
    )]
    public function add(array $fields): DeliveryAddResult
    {
        return new DeliveryAddResult(
            $this->core->call('sale.delivery.add', $fields)
        );
    }

    /**
     * Updates a delivery service.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-update.html
     *
     * @param int   $id     Delivery service identifier
     * @param array $fields Field values for update
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.update',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-update.html',
        'Updates a delivery service.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('sale.delivery.update', [
                'ID' => $id,
                'FIELDS' => $fields,
            ])
        );
    }

    /**
     * Returns a list of delivery services.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-get-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter object
     * @param array $order  Sorting object
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.getlist',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-get-list.html',
        'Returns a list of delivery services.'
    )]
    public function getlist(array $select = [], array $filter = [], array $order = []): DeliveriesResult
    {
        return new DeliveriesResult(
            $this->core->call('sale.delivery.getlist', [
                'SELECT' => $select,
                'FILTER' => $filter,
                'ORDER' => $order,
            ])
        );
    }

    /**
     * Deletes a delivery service.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-delete.html',
        'Deletes a delivery service.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.delivery.delete', [
                'ID' => $id,
            ])
        );
    }

    /**
     * Updates delivery service settings.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-config-update.html
     *
     * @param int   $id     Delivery service identifier
     * @param array $config Array of settings with CODE and VALUE fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.config.update',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-config-update.html',
        'Updates delivery service settings.'
    )]
    public function configUpdate(int $id, array $config): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('sale.delivery.config.update', [
                'ID' => $id,
                'CONFIG' => $config,
            ])
        );
    }

    /**
     * Returns delivery service settings.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-config-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.config.get',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-config-get.html',
        'Returns delivery service settings.'
    )]
    public function configGet(int $id): DeliveryConfigGetResult
    {
        return new DeliveryConfigGetResult(
            $this->core->call('sale.delivery.config.get', [
                'ID' => $id,
            ])
        );
    }
}
