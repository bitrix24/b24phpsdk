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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\DeliveryRequest\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\DeliveryRequest\Service\Batch;
use Bitrix24\SDK\Services\Sale\DeliveryRequest\Service\DeliveryRequest;
use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use Bitrix24\SDK\Tests\Integration\Services\Sale\SaleBatchFixturesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Delivery requests are created by the external delivery service, they can not be created with REST API.
 * The tests check that batch commands reach the API with a valid delivery request identifier
 * and the API looks up the delivery request itself.
 */
#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\DeliveryRequest\Batch::class)]
class BatchTest extends TestCase
{
    use SaleBatchFixturesTrait;

    private SaleServiceBuilder $saleServiceBuilder;

    private DeliveryRequest $deliveryRequestService;

    private int $handlerId;

    private int $deliveryId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->saleServiceBuilder = Fabric::getServiceBuilder()->getSaleScope();
        $this->deliveryRequestService = $this->saleServiceBuilder->deliveryRequest();
        $this->handlerId = $this->createDeliveryHandler('batch_delivery_handler_' . uniqid());
        $this->deliveryId = $this->createDelivery($this->getDeliveryHandlerCode($this->handlerId));
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->cleanup(fn (): \Bitrix24\SDK\Core\Result\DeletedItemResult => $this->saleServiceBuilder->delivery()->delete($this->deliveryId));
        $this->cleanup(fn (): \Bitrix24\SDK\Core\Result\DeletedItemResult => $this->saleServiceBuilder->deliveryHandler()->delete($this->handlerId));
    }

    /**
     * @throws TransportException
     */
    #[TestDox('Batch::update sends delivery request identifier to the API')]
    public function testUpdateUnknownRequest(): void
    {
        $this->expectException(BaseException::class);
        $this->expectExceptionMessageMatches('/request_not_found/i');

        iterator_to_array($this->deliveryRequestService->batch->update([
            [
                'DELIVERY_ID' => $this->deliveryId,
                'REQUEST_ID' => 'batch_unknown_request_' . uniqid(),
                'STATUS' => ['TEXT' => 'In progress', 'SEMANTIC' => 'process'],
            ],
        ]));
    }

    /**
     * @throws TransportException
     */
    #[TestDox('Batch::delete sends delivery request identifier to the API')]
    public function testDeleteUnknownRequest(): void
    {
        $this->expectException(BaseException::class);
        $this->expectExceptionMessageMatches('/request_not_found/i');

        iterator_to_array($this->deliveryRequestService->batch->delete([
            [
                'DELIVERY_ID' => $this->deliveryId,
                'REQUEST_ID' => 'batch_unknown_request_' . uniqid(),
            ],
        ]));
    }
}
