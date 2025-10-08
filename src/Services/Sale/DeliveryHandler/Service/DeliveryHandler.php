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

namespace Bitrix24\SDK\Services\Sale\DeliveryHandler\Service;

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
use Bitrix24\SDK\Services\Sale\DeliveryHandler\Result\DeliveryHandlersResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class DeliveryHandler extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a delivery service handler.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-add.html
     *
     * @param array $fields Field values for creating a delivery service handler
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.handler.add',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-add.html',
        'Adds a delivery service handler.'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('sale.delivery.handler.add', $fields)
        );
    }

    /**
     * Updates the delivery service handler.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-update.html
     *
     * @param int   $id     Delivery service handler identifier
     * @param array $fields Field values for update
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.handler.update',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-update.html',
        'Updates the delivery service handler.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('sale.delivery.handler.update', array_merge(['ID' => $id], $fields))
        );
    }

    /**
     * Returns a list of delivery service handlers.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.handler.list',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-list.html',
        'Returns a list of delivery service handlers.'
    )]
    public function list(): DeliveryHandlersResult
    {
        return new DeliveryHandlersResult(
            $this->core->call('sale.delivery.handler.list')
        );
    }

    /**
     * Deletes a delivery service handler.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.handler.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-delete.html',
        'Deletes a delivery service handler.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.delivery.handler.delete', [
                'ID' => $id,
            ])
        );
    }
}
