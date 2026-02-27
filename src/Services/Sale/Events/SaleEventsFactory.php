<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Events;

use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Sale\Events\OnSaleOrderSaved\OnSaleOrderSaved;
use Bitrix24\SDK\Services\Sale\Events\OnSaleBeforeOrderDelete\OnSaleBeforeOrderDelete;
use Bitrix24\SDK\Services\Sale\Events\OnPropertyValueEntitySaved\OnPropertyValueEntitySaved;
use Bitrix24\SDK\Services\Sale\Events\OnPaymentEntitySaved\OnPaymentEntitySaved;
use Bitrix24\SDK\Services\Sale\Events\OnShipmentEntitySaved\OnShipmentEntitySaved;
use Bitrix24\SDK\Services\Sale\Events\OnOrderEntitySaved\OnOrderEntitySaved;
use Bitrix24\SDK\Services\Sale\Events\OnPropertyValueDeleted\OnPropertyValueDeleted;
use Bitrix24\SDK\Services\Sale\Events\OnPaymentDeleted\OnPaymentDeleted;
use Bitrix24\SDK\Services\Sale\Events\OnShipmentDeleted\OnShipmentDeleted;
use Bitrix24\SDK\Services\Sale\Events\OnOrderDeleted\OnOrderDeleted;
use Symfony\Component\HttpFoundation\Request;

readonly class SaleEventsFactory implements EventsFabricInterface
{
    #[\Override]
    public function isSupport(string $eventCode): bool
    {
        return in_array(strtoupper($eventCode), [
            OnSaleOrderSaved::CODE,
            OnSaleBeforeOrderDelete::CODE,
            OnPropertyValueEntitySaved::CODE,
            OnPaymentEntitySaved::CODE,
            OnShipmentEntitySaved::CODE,
            OnOrderEntitySaved::CODE,
            OnPropertyValueDeleted::CODE,
            OnPaymentDeleted::CODE,
            OnShipmentDeleted::CODE,
            OnOrderDeleted::CODE,
        ], true);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    public function create(Request $eventRequest): EventInterface
    {
        $eventPayload = $eventRequest->request->all();
        if (!array_key_exists('event', $eventPayload)) {
            throw new InvalidArgumentException('«event» key not found in event payload');
        }

        return match ($eventPayload['event']) {
            OnSaleOrderSaved::CODE => new OnSaleOrderSaved($eventRequest),
            OnSaleBeforeOrderDelete::CODE => new OnSaleBeforeOrderDelete($eventRequest),
            OnPropertyValueEntitySaved::CODE => new OnPropertyValueEntitySaved($eventRequest),
            OnPaymentEntitySaved::CODE => new OnPaymentEntitySaved($eventRequest),
            OnShipmentEntitySaved::CODE => new OnShipmentEntitySaved($eventRequest),
            OnOrderEntitySaved::CODE => new OnOrderEntitySaved($eventRequest),
            OnPropertyValueDeleted::CODE => new OnPropertyValueDeleted($eventRequest),
            OnPaymentDeleted::CODE => new OnPaymentDeleted($eventRequest),
            OnShipmentDeleted::CODE => new OnShipmentDeleted($eventRequest),
            OnOrderDeleted::CODE => new OnOrderDeleted($eventRequest),
            default => throw new InvalidArgumentException(
                sprintf('Unexpected event code «%s»', $eventPayload['event'])
            ),
        };
    }
}
