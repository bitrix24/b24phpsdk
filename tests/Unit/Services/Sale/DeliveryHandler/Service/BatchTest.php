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

namespace Bitrix24\SDK\Tests\Unit\Services\Sale\DeliveryHandler\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Sale\DeliveryHandler\Batch as DeliveryHandlerBatch;
use Bitrix24\SDK\Services\Sale\DeliveryHandler\Result\DeliveryHandlerItemResult;
use Bitrix24\SDK\Services\Sale\DeliveryHandler\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Services\Sale\BatchCoreStubTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
#[CoversClass(DeliveryHandlerBatch::class)]
class BatchTest extends TestCase
{
    use BatchCoreStubTrait;

    #[Test]
    #[TestDox('add() sends handler fields as top-level parameters')]
    public function testAddSendsTopLevelParameters(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): int => 8);

        $results = iterator_to_array($this->makeBatch($core)->add([['NAME' => 'Handler', 'CODE' => 'code']]), false);

        $this->assertInstanceOf(AddedItemBatchResult::class, $results[0]);
        $this->assertSame(8, $results[0]->getId());
        $this->assertSame(['sale.delivery.handler.add', ['NAME' => 'Handler', 'CODE' => 'code']], $this->capturedCalls[0]);
    }

    #[Test]
    #[TestDox('update() sends uppercase "ID" with fields as top-level parameters')]
    public function testUpdateSendsUppercaseIdWithTopLevelFields(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array($this->makeBatch($core)->update([8 => ['NAME' => 'New']]), false);

        $this->assertInstanceOf(UpdatedItemBatchResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccess());
        $this->assertSame(['sale.delivery.handler.update', ['ID' => '8', 'NAME' => 'New']], $this->capturedCalls[0]);
    }

    #[Test]
    #[TestDox('delete() sends uppercase "ID" parameter')]
    public function testDeleteSendsUppercaseId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array($this->makeBatch($core)->delete([8]), false);

        $this->assertInstanceOf(DeletedItemBatchResult::class, $results[0]);
        $this->assertSame(['sale.delivery.handler.delete', ['ID' => '8']], $this->capturedCalls[0]);
    }

    #[Test]
    #[TestDox('list() reads all handlers in a single call and respects limit')]
    public function testListReadsAllHandlersInSingleCall(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): array => [
            ['ID' => '1', 'CODE' => 'a'],
            ['ID' => '2', 'CODE' => 'b'],
        ]);

        $items = iterator_to_array($this->makeBatch($core)->list(1), false);

        $this->assertCount(1, $items);
        $this->assertInstanceOf(DeliveryHandlerItemResult::class, $items[0]);
        $this->assertSame([['sale.delivery.handler.list', []]], $this->capturedCalls);
    }

    private function makeBatch(CoreInterface $core): Batch
    {
        return new Batch(new DeliveryHandlerBatch($core, new NullLogger()), new NullLogger());
    }
}
