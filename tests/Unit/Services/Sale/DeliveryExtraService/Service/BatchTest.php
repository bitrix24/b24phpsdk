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

namespace Bitrix24\SDK\Tests\Unit\Services\Sale\DeliveryExtraService\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Sale\DeliveryExtraService\Batch as DeliveryExtraServiceBatch;
use Bitrix24\SDK\Services\Sale\DeliveryExtraService\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Services\Sale\BatchCoreStubTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
#[CoversClass(DeliveryExtraServiceBatch::class)]
class BatchTest extends TestCase
{
    use BatchCoreStubTrait;

    #[Test]
    #[TestDox('add() sends extra service fields as top-level parameters')]
    public function testAddSendsTopLevelParameters(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): int => 12);

        $results = iterator_to_array(
            $this->makeBatch($core)->add([['DELIVERY_ID' => 30, 'TYPE' => 'checkbox', 'NAME' => 'Door delivery']]),
            false
        );

        $this->assertInstanceOf(AddedItemBatchResult::class, $results[0]);
        $this->assertSame(12, $results[0]->getId());
        $this->assertSame(
            ['sale.delivery.extra.service.add', ['DELIVERY_ID' => '30', 'TYPE' => 'checkbox', 'NAME' => 'Door delivery']],
            $this->capturedCalls[0]
        );
    }

    #[Test]
    #[TestDox('update() sends uppercase "ID" with fields as top-level parameters')]
    public function testUpdateSendsUppercaseIdWithTopLevelFields(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array($this->makeBatch($core)->update([12 => ['NAME' => 'New', 'PRICE' => 15]]), false);

        $this->assertInstanceOf(UpdatedItemBatchResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccess());
        $this->assertSame(
            ['sale.delivery.extra.service.update', ['ID' => '12', 'NAME' => 'New', 'PRICE' => '15']],
            $this->capturedCalls[0]
        );
    }

    #[Test]
    #[TestDox('delete() sends uppercase "ID" parameter')]
    public function testDeleteSendsUppercaseId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array($this->makeBatch($core)->delete([12]), false);

        $this->assertInstanceOf(DeletedItemBatchResult::class, $results[0]);
        $this->assertSame(['sale.delivery.extra.service.delete', ['ID' => '12']], $this->capturedCalls[0]);
    }

    private function makeBatch(CoreInterface $core): Batch
    {
        return new Batch(new DeliveryExtraServiceBatch($core, new NullLogger()), new NullLogger());
    }
}
