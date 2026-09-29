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

namespace Bitrix24\SDK\Tests\Unit\Services\Sale\DeliveryRequest\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Sale\DeliveryRequest\Batch as DeliveryRequestBatch;
use Bitrix24\SDK\Services\Sale\DeliveryRequest\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Services\Sale\BatchCoreStubTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
#[CoversClass(DeliveryRequestBatch::class)]
class BatchTest extends TestCase
{
    use BatchCoreStubTrait;

    #[Test]
    #[TestDox('update() sends request fields as top-level parameters')]
    public function testUpdateSendsTopLevelParameters(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array(
            $this->makeBatch($core)->update([
                ['DELIVERY_ID' => 30, 'REQUEST_ID' => 'r-1', 'FINALIZE' => 'Y'],
                ['DELIVERY_ID' => 30, 'REQUEST_ID' => 'r-2', 'STATUS' => ['TEXT' => 'Done', 'SEMANTIC' => 'success']],
            ]),
            false
        );

        $this->assertCount(2, $results);
        $this->assertInstanceOf(UpdatedItemBatchResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccess());
        $this->assertSame(
            ['sale.delivery.request.update', ['DELIVERY_ID' => '30', 'REQUEST_ID' => 'r-1', 'FINALIZE' => 'Y']],
            $this->capturedCalls[0]
        );
        $this->assertSame(
            ['sale.delivery.request.update', ['DELIVERY_ID' => '30', 'REQUEST_ID' => 'r-2', 'STATUS' => ['TEXT' => 'Done', 'SEMANTIC' => 'success']]],
            $this->capturedCalls[1]
        );
    }

    #[Test]
    #[TestDox('delete() sends "DELIVERY_ID" and "REQUEST_ID" parameters')]
    public function testDeleteSendsDeliveryIdAndRequestId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array(
            $this->makeBatch($core)->delete([['DELIVERY_ID' => 30, 'REQUEST_ID' => 'r-1', 'EXTRA' => 'ignored']]),
            false
        );

        $this->assertInstanceOf(DeletedItemBatchResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccess());
        $this->assertSame(['sale.delivery.request.delete', ['DELIVERY_ID' => '30', 'REQUEST_ID' => 'r-1']], $this->capturedCalls[0]);
    }

    #[Test]
    #[TestDox('delete() rejects items without "REQUEST_ID"')]
    public function testDeleteRejectsItemWithoutRequestId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        iterator_to_array($this->makeBatch($core)->delete([['DELIVERY_ID' => 30]]));
    }

    #[Test]
    #[TestDox('update() rejects items without integer "DELIVERY_ID"')]
    public function testUpdateRejectsItemWithoutDeliveryId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        iterator_to_array($this->makeBatch($core)->update([['DELIVERY_ID' => '30', 'REQUEST_ID' => 'r-1']]));
    }

    private function makeBatch(CoreInterface $core): Batch
    {
        return new Batch(new DeliveryRequestBatch($core, new NullLogger()), new NullLogger());
    }
}
