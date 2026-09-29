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

namespace Bitrix24\SDK\Services\Sale;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;

/**
 * Class SaleServiceBuilder
 *
 * @package Bitrix24\SDK\Services\Sale
 */
#[ApiServiceBuilderMetadata(new Scope(['sale']))]
class SaleServiceBuilder extends AbstractServiceBuilder
{
    /**
     * Get TradePlatform service
     */
    public function tradePlatform(): TradePlatform\Service\TradePlatform
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new TradePlatform\Service\TradePlatform(
                $this->core,
                $this->log,
                new TradePlatform\Service\Batch(
                    new TradePlatform\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Order properties service (sale.property.*)
     */
    public function property(): Property\Service\Property
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Property\Service\Property(
                $this->core,
                $this->log,
                new Property\Service\Batch(
                    new Property\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function propertyGroup(): PropertyGroup\Service\PropertyGroup
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new PropertyGroup\Service\PropertyGroup(
                $this->core,
                $this->log,
                new PropertyGroup\Service\Batch(
                    new PropertyGroup\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Order service (sale.order.*)
     */
    public function order(): Order\Service\Order
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $batch = new Order\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Order\Service\Order(
                new Order\Service\Batch($batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function status(): Status\Service\Status
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Status\Service\Status(
                $this->core,
                $this->log,
                new Status\Service\Batch(
                    new Status\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function personTypeStatus(): PersonTypeStatus\Service\PersonTypeStatus
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new PersonTypeStatus\Service\PersonTypeStatus(
                $this->core,
                $this->log,
                new PersonTypeStatus\Service\Batch(
                    new PersonTypeStatus\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function personType(): PersonType\Service\PersonType
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new PersonType\Service\PersonType(
                $this->core,
                $this->log,
                new PersonType\Service\Batch(
                    new PersonType\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Payment service (sale.payment.*)
     */
    public function payment(): Payment\Service\Payment
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Payment\Service\Payment(
                $this->core,
                $this->log,
                new Payment\Service\Batch(
                    new Payment\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Payment Item Basket service (sale.paymentitembasket.*)
     */
    public function paymentItemBasket(): PaymentItemBasket\Service\PaymentItemBasket
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new PaymentItemBasket\Service\PaymentItemBasket(
                $this->core,
                $this->log,
                new PaymentItemBasket\Service\Batch(
                    new PaymentItemBasket\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Payment Item Shipment service (sale.paymentitemshipment.*)
     */
    public function paymentItemShipment(): PaymentItemShipment\Service\PaymentItemShipment
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new PaymentItemShipment\Service\PaymentItemShipment(
                $this->core,
                $this->log,
                new PaymentItemShipment\Service\Batch(
                    new PaymentItemShipment\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function propertyVariant(): PropertyVariant\Service\PropertyVariant
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new PropertyVariant\Service\PropertyVariant(
                $this->core,
                $this->log,
                new PropertyVariant\Service\Batch(
                    new PropertyVariant\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function statusLang(): StatusLang\Service\StatusLang
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new StatusLang\Service\StatusLang(
                $this->core,
                $this->log,
                new StatusLang\Service\Batch(
                    new StatusLang\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Shipment service (sale.shipment.*)
     */
    public function shipment(): Shipment\Service\Shipment
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Shipment\Service\Shipment(
                $this->core,
                $this->log,
                new Shipment\Service\Batch(
                    new Shipment\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * ShipmentProperty service (sale.shipmentproperty.*)
     */
    public function shipmentProperty(): ShipmentProperty\Service\ShipmentProperty
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new ShipmentProperty\Service\ShipmentProperty(
                $this->core,
                $this->log,
                new ShipmentProperty\Service\Batch(
                    new ShipmentProperty\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Basket Item service (sale.basketitem.*)
     */
    public function basketItem(): BasketItem\Service\BasketItem
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $batch = new BasketItem\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new BasketItem\Service\BasketItem(
                new BasketItem\Service\Batch($batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * ShipmentPropertyValue service (sale.shipmentpropertyvalue.*)
     */
    public function shipmentPropertyValue(): ShipmentPropertyValue\Service\ShipmentPropertyValue
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new ShipmentPropertyValue\Service\ShipmentPropertyValue(
                $this->core,
                $this->log,
                new ShipmentPropertyValue\Service\Batch(
                    new ShipmentPropertyValue\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * ShipmentItem service (sale.shipmentitem.*)
     */
    public function shipmentItem(): ShipmentItem\Service\ShipmentItem
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new ShipmentItem\Service\ShipmentItem(
                $this->core,
                $this->log,
                new ShipmentItem\Service\Batch(
                    new ShipmentItem\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * BasketProperty service (sale.basketproperties.*)
     */
    public function basketProperty(): BasketProperty\Service\BasketProperty
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new BasketProperty\Service\BasketProperty(
                $this->core,
                $this->log,
                new BasketProperty\Service\Batch(
                    new BasketProperty\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Cash register handlers service (sale.cashbox.handler.*)
     */
    public function cashboxHandler(): CashboxHandler\Service\CashboxHandler
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new CashboxHandler\Service\CashboxHandler(
                $this->core,
                $this->log,
                new CashboxHandler\Service\Batch(
                    new CashboxHandler\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * DeliveryHandler service (sale.delivery.handler.*)
     */
    public function deliveryHandler(): DeliveryHandler\Service\DeliveryHandler
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new DeliveryHandler\Service\DeliveryHandler(
                $this->core,
                $this->log,
                new DeliveryHandler\Service\Batch(
                    new DeliveryHandler\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Cash registers service (sale.cashbox.*)
     */
    public function cashbox(): Cashbox\Service\Cashbox
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Cashbox\Service\Cashbox(
                $this->core,
                $this->log,
                new Cashbox\Service\Batch(
                    new Cashbox\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Delivery service (sale.delivery.*)
     */
    public function delivery(): Delivery\Service\Delivery
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Delivery\Service\Delivery(
                $this->core,
                $this->log,
                new Delivery\Service\Batch(
                    new Delivery\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Delivery request service (sale.delivery.request.*)
     */
    public function deliveryRequest(): DeliveryRequest\Service\DeliveryRequest
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new DeliveryRequest\Service\DeliveryRequest(
                $this->core,
                $this->log,
                new DeliveryRequest\Service\Batch(
                    new DeliveryRequest\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Delivery extra service (sale.delivery.extra.service.*)
     */
    public function deliveryExtraService(): DeliveryExtraService\Service\DeliveryExtraService
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new DeliveryExtraService\Service\DeliveryExtraService(
                $this->core,
                $this->log,
                new DeliveryExtraService\Service\Batch(
                    new DeliveryExtraService\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Property Relation service (sale.propertyRelation.*)
     */
    public function propertyRelation(): PropertyRelation\Service\PropertyRelation
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new PropertyRelation\Service\PropertyRelation(
                $this->core,
                $this->log,
                new PropertyRelation\Service\Batch(
                    new PropertyRelation\Batch($this->core, $this->log),
                    $this->log
                )
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
