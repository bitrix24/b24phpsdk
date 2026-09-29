<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services;

use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ServiceConstructorCompatibilityTest extends TestCase
{
    /** @param class-string<AbstractService> $serviceClass */
    #[DataProvider('serviceClasses')]
    public function testExistingTwoArgumentConstruction(string $serviceClass, string $batchClass, string $loggerParameter): void
    {
        $nullCore = new NullCore();
        $service = new $serviceClass($nullCore, new NullLogger());

        self::assertSame($nullCore, $service->core);
        self::assertInstanceOf($batchClass, (new \ReflectionProperty($service, 'batch'))->getValue($service));

        $namedService = new $serviceClass(...['core' => $nullCore, $loggerParameter => new NullLogger()]);
        self::assertSame($nullCore, $namedService->core);
    }

    public static function serviceClasses(): array
    {
        return [
            [\Bitrix24\SDK\Services\Booking\Booking\Service\Booking::class, \Bitrix24\SDK\Services\Booking\Booking\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Booking\BookingClient\Service\BookingClient::class, \Bitrix24\SDK\Services\Booking\BookingClient\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Booking\BookingExternalData\Service\BookingExternalData::class, \Bitrix24\SDK\Services\Booking\BookingExternalData\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Booking\ClientType\Service\ClientType::class, \Bitrix24\SDK\Services\Booking\ClientType\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Booking\Resource\Service\Resource::class, \Bitrix24\SDK\Services\Booking\Resource\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Booking\ResourceSlots\Service\ResourceSlots::class, \Bitrix24\SDK\Services\Booking\ResourceSlots\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Booking\ResourceType\Service\ResourceType::class, \Bitrix24\SDK\Services\Booking\ResourceType\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Booking\Waitlist\Service\Waitlist::class, \Bitrix24\SDK\Services\Booking\Waitlist\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Booking\WaitlistClient\Service\WaitlistClient::class, \Bitrix24\SDK\Services\Booking\WaitlistClient\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Booking\WaitlistExternalData\Service\WaitlistExternalData::class, \Bitrix24\SDK\Services\Booking\WaitlistExternalData\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog::class, \Bitrix24\SDK\Services\Catalog\Catalog\Service\Batch::class, 'log'],
            [\Bitrix24\SDK\Services\Catalog\Extra\Service\Extra::class, \Bitrix24\SDK\Services\Catalog\Extra\Service\Batch::class, 'log'],
            [\Bitrix24\SDK\Services\Catalog\Measure\Service\Measure::class, \Bitrix24\SDK\Services\Catalog\Measure\Service\Batch::class, 'log'],
            [\Bitrix24\SDK\Services\Catalog\Product\Offer\Service\Offer::class, \Bitrix24\SDK\Services\Catalog\Product\Offer\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Catalog\Product\ProductService\Service\ProductService::class, \Bitrix24\SDK\Services\Catalog\Product\ProductService\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Catalog\Product\Sku\Service\Sku::class, \Bitrix24\SDK\Services\Catalog\Product\Sku\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Catalog\ProductPropertySection\Service\ProductPropertySection::class, \Bitrix24\SDK\Services\Catalog\ProductPropertySection\Service\Batch::class, 'log'],
            [\Bitrix24\SDK\Services\Sale\BasketProperty\Service\BasketProperty::class, \Bitrix24\SDK\Services\Sale\BasketProperty\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\Cashbox\Service\Cashbox::class, \Bitrix24\SDK\Services\Sale\Cashbox\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\CashboxHandler\Service\CashboxHandler::class, \Bitrix24\SDK\Services\Sale\CashboxHandler\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\Delivery\Service\Delivery::class, \Bitrix24\SDK\Services\Sale\Delivery\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\DeliveryExtraService\Service\DeliveryExtraService::class, \Bitrix24\SDK\Services\Sale\DeliveryExtraService\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\DeliveryHandler\Service\DeliveryHandler::class, \Bitrix24\SDK\Services\Sale\DeliveryHandler\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\DeliveryRequest\Service\DeliveryRequest::class, \Bitrix24\SDK\Services\Sale\DeliveryRequest\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\Payment\Service\Payment::class, \Bitrix24\SDK\Services\Sale\Payment\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\PaymentItemBasket\Service\PaymentItemBasket::class, \Bitrix24\SDK\Services\Sale\PaymentItemBasket\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\PaymentItemShipment\Service\PaymentItemShipment::class, \Bitrix24\SDK\Services\Sale\PaymentItemShipment\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\PersonType\Service\PersonType::class, \Bitrix24\SDK\Services\Sale\PersonType\Service\Batch::class, 'log'],
            [\Bitrix24\SDK\Services\Sale\PersonTypeStatus\Service\PersonTypeStatus::class, \Bitrix24\SDK\Services\Sale\PersonTypeStatus\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\Property\Service\Property::class, \Bitrix24\SDK\Services\Sale\Property\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\PropertyGroup\Service\PropertyGroup::class, \Bitrix24\SDK\Services\Sale\PropertyGroup\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\PropertyRelation\Service\PropertyRelation::class, \Bitrix24\SDK\Services\Sale\PropertyRelation\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\PropertyVariant\Service\PropertyVariant::class, \Bitrix24\SDK\Services\Sale\PropertyVariant\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\Shipment\Service\Shipment::class, \Bitrix24\SDK\Services\Sale\Shipment\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\ShipmentItem\Service\ShipmentItem::class, \Bitrix24\SDK\Services\Sale\ShipmentItem\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\ShipmentProperty\Service\ShipmentProperty::class, \Bitrix24\SDK\Services\Sale\ShipmentProperty\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Service\ShipmentPropertyValue::class, \Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\Status\Service\Status::class, \Bitrix24\SDK\Services\Sale\Status\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\StatusLang\Service\StatusLang::class, \Bitrix24\SDK\Services\Sale\StatusLang\Service\Batch::class, 'logger'],
            [\Bitrix24\SDK\Services\Sale\TradePlatform\Service\TradePlatform::class, \Bitrix24\SDK\Services\Sale\TradePlatform\Service\Batch::class, 'log'],
        ];
    }
}
