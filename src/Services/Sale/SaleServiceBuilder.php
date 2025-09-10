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
use Bitrix24\SDK\Services\Sale\TradePlatform\Service\TradePlatform;
use Bitrix24\SDK\Services\Sale\Property\Service\Property;
use Bitrix24\SDK\Services\Sale\PropertyVariant\Service\PropertyVariant;
use Bitrix24\SDK\Services\Sale\Status\Service\Status;
use Bitrix24\SDK\Services\Sale\StatusLang\Service\StatusLang;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Service\PersonTypeStatus;

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
    public function tradePlatform(): TradePlatform
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new TradePlatform(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Order properties service (sale.property.*)
     */
    public function property(): Property
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Property(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function propertyGroup(): PropertyGroup\Service\PropertyGroup
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new PropertyGroup\Service\PropertyGroup(
                $this->core,
                $this->log
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

    public function status(): Status
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Status(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function personTypeStatus(): PersonTypeStatus
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new PersonTypeStatus(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function personType(): PersonType\Service\PersonType
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new PersonType\Service\PersonType(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function propertyVariant(): PropertyVariant
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new PropertyVariant(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function statusLang(): StatusLang
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new StatusLang(
                $this->core,
                $this->log
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
                $this->log
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
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

}
