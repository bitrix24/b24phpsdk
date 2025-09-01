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

namespace Bitrix24\SDK\Services\Sale\Order\Service;

use Bitrix24\SDK\Core\CoreInterface;
use Bitrix24\SDK\Services\Sale\Order\Result\OrderResult;
use Bitrix24\SDK\Services\Sale\Order\Result\OrdersResult;
use Bitrix24\SDK\Services\Sale\Order\Result\OrderFieldsResult;
use Bitrix24\SDK\Services\Sale\Order\Result\OrderAddedResult;
use Bitrix24\SDK\Services\Sale\Order\Result\OrderUpdatedResult;
use Psr\Log\LoggerInterface;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;

#[ApiServiceMetadata(new \Bitrix24\SDK\Core\Credentials\Scope(['sale']))]
class Order extends \Bitrix24\SDK\Services\AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    #[ApiEndpointMetadata(
        'sale.order.add',
        'https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-add.html',
    'Creates a new order.'
    )]
    public function add(array $fields): OrderAddedResult
    {
        $response = $this->core->call('sale.order.add', [
            'fields' => $fields
        ]);
        return new OrderAddedResult($response);
    }

    #[ApiEndpointMetadata(
        'sale.order.update',
        'https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-update.html',
    'Updates an existing order.'
    )]
    public function update(int $id, array $fields): OrderUpdatedResult
    {
        $response = $this->core->call('sale.order.update', [
            'id' => $id,
            'fields' => $fields
        ]);
        return new OrderUpdatedResult($response);
    }

    #[ApiEndpointMetadata(
        'sale.order.get',
        'https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-get.html',
    'Retrieves information about an order.'
    )]
    public function get(int $id): OrderResult
    {
        $response = $this->core->call('sale.order.get', [
            'id' => $id
        ]);
        return new OrderResult($response);
    }

    #[ApiEndpointMetadata(
        'sale.order.list',
        'https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-list.html',
    'Retrieves a list of orders.'
    )]
    public function list(array $filter = [], array $order = [], array $select = [], int $start = 0): OrdersResult
    {
        $response = $this->core->call('sale.order.list', [
            'filter' => $filter,
            'order' => $order,
            'select' => $select,
            'start' => $start
        ]);
        return new OrdersResult($response);
    }

    #[ApiEndpointMetadata(
        'sale.order.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-delete.html',
    'Deletes an order.'
    )]
    public function delete(int $id): OrderResult
    {
        $response = $this->core->call('sale.order.delete', [
            'id' => $id
        ]);
        return new OrderResult($response);
    }

    #[ApiEndpointMetadata(
        'sale.order.getFields',
        'https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-getfields.html',
    'Retrieves the description of order fields.'
    )]
    public function getFields(): OrderFieldsResult
    {
        $response = $this->core->call('sale.order.getFields', []);
        return new OrderFieldsResult($response);
    }
}
