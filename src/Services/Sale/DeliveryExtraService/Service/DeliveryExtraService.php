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

namespace Bitrix24\SDK\Services\Sale\DeliveryExtraService\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\DeliveryExtraService\Result\DeliveryExtraServicesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class DeliveryExtraService extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a delivery service.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/extra-service/sale-delivery-extra-service-add.html
     *
     * @param array $fields Field values for creating a delivery extra service
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.extra.service.add',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/extra-service/sale-delivery-extra-service-add.html',
        'Adds a delivery service.'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('sale.delivery.extra.service.add', $fields)
        );
    }

    /**
     * Updates a delivery service.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/extra-service/sale-delivery-extra-service-update.html
     *
     * @param int   $id     Delivery extra service identifier
     * @param array $fields Field values for update
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.extra.service.update',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/extra-service/sale-delivery-extra-service-update.html',
        'Updates a delivery service.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('sale.delivery.extra.service.update', [
                'ID' => $id,
            ] + $fields)
        );
    }

    /**
     * Returns information about all services of a specific delivery service.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/extra-service/sale-delivery-extra-service-get.html
     *
     * @param int $deliveryId Identifier of the delivery service
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.extra.service.get',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/extra-service/sale-delivery-extra-service-get.html',
        'Returns information about all services of a specific delivery service.'
    )]
    public function get(int $deliveryId): DeliveryExtraServicesResult
    {
        return new DeliveryExtraServicesResult(
            $this->core->call('sale.delivery.extra.service.get', [
                'DELIVERY_ID' => $deliveryId,
            ])
        );
    }

    /**
     * Deletes a delivery service.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/extra-service/sale-delivery-extra-service-delete.html
     *
     * @param int $id Delivery extra service identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.extra.service.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/extra-service/sale-delivery-extra-service-delete.html',
        'Deletes a delivery service.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.delivery.extra.service.delete', [
                'ID' => $id,
            ])
        );
    }
}
