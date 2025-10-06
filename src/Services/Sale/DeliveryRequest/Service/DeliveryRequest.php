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

namespace Bitrix24\SDK\Services\Sale\DeliveryRequest\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\DeliveryRequest\Result\DeliveryRequestSendMessageResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale', 'delivery']))]
class DeliveryRequest extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Updates the delivery request.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery-request/sale-delivery-request-update.html
     *
     * @param array  $fields  Field values for updating the delivery request
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.request.update',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery-request/sale-delivery-request-update.html',
        'Updates the delivery request.'
    )]
    public function update(array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('sale.delivery.request.update', $fields)
        );
    }

    /**
     * Creates notifications for the delivery request.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery-request/sale-delivery-request-send-message.html
     *
     * @param int    $deliveryId  Identifier of the delivery service related to the delivery request
     * @param string $requestId   Identifier of the delivery request
     * @param string $addressee   Recipient of the message (MANAGER/RECIPIENT)
     * @param array  $message     Message data
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.request.sendmessage',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery-request/sale-delivery-request-send-message.html',
        'Creates notifications for the delivery request.'
    )]
    public function sendMessage(
        int $deliveryId,
        string $requestId,
        string $addressee,
        array $message
    ): DeliveryRequestSendMessageResult {
        return new DeliveryRequestSendMessageResult(
            $this->core->call('sale.delivery.request.sendmessage', [
                'DELIVERY_ID' => $deliveryId,
                'REQUEST_ID' => $requestId,
                'ADDRESSEE' => $addressee,
                'MESSAGE' => $message,
            ])
        );
    }

    /**
     * Deletes the delivery request.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery-request/sale-delivery-request-delete.html
     *
     * @param int    $deliveryId  Identifier of the delivery service to which the delivery request belongs
     * @param string $requestId   Identifier of the delivery request
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.delivery.request.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery-request/sale-delivery-request-delete.html',
        'Deletes the delivery request.'
    )]
    public function delete(int $deliveryId, string $requestId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.delivery.request.delete', [
                'DELIVERY_ID' => $deliveryId,
                'REQUEST_ID' => $requestId,
            ])
        );
    }
}
