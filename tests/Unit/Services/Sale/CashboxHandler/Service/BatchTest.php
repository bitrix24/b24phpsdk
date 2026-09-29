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

namespace Bitrix24\SDK\Tests\Unit\Services\Sale\CashboxHandler\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Sale\CashboxHandler\Batch as CashboxHandlerBatch;
use Bitrix24\SDK\Services\Sale\CashboxHandler\Result\CashboxHandlerItemResult;
use Bitrix24\SDK\Services\Sale\CashboxHandler\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Services\Sale\BatchCoreStubTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
#[CoversClass(CashboxHandlerBatch::class)]
class BatchTest extends TestCase
{
    use BatchCoreStubTrait;

    #[Test]
    #[TestDox('add() sends handler fields as top-level parameters')]
    public function testAddSendsTopLevelParameters(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): int => 4);

        $results = iterator_to_array(
            $this->makeBatch($core)->add([['CODE' => 'code', 'NAME' => 'Handler', 'SETTINGS' => ['PRINT_URL' => 'https://example.com']]]),
            false
        );

        $this->assertInstanceOf(AddedItemBatchResult::class, $results[0]);
        $this->assertSame(4, $results[0]->getId());
        $this->assertSame(
            ['sale.cashbox.handler.add', ['CODE' => 'code', 'NAME' => 'Handler', 'SETTINGS' => ['PRINT_URL' => 'https://example.com']]],
            $this->capturedCalls[0]
        );
    }

    #[Test]
    #[TestDox('update() sends uppercase "ID" and "FIELDS" parameters')]
    public function testUpdateSendsUppercaseIdAndFields(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array($this->makeBatch($core)->update([4 => ['NAME' => 'New']]), false);

        $this->assertInstanceOf(UpdatedItemBatchResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccess());
        $this->assertSame(['sale.cashbox.handler.update', ['ID' => '4', 'FIELDS' => ['NAME' => 'New']]], $this->capturedCalls[0]);
    }

    #[Test]
    #[TestDox('delete() sends uppercase "ID" parameter')]
    public function testDeleteSendsUppercaseId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array($this->makeBatch($core)->delete([4]), false);

        $this->assertInstanceOf(DeletedItemBatchResult::class, $results[0]);
        $this->assertSame(['sale.cashbox.handler.delete', ['ID' => '4']], $this->capturedCalls[0]);
    }

    #[Test]
    #[TestDox('list() reads all handlers in a single call without parameters')]
    public function testListReadsAllHandlersInSingleCall(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): array => [
            ['ID' => '1', 'CODE' => 'a'],
            ['ID' => '2', 'CODE' => 'b'],
        ]);

        $items = iterator_to_array($this->makeBatch($core)->list(), false);

        $this->assertCount(2, $items);
        $this->assertInstanceOf(CashboxHandlerItemResult::class, $items[1]);
        $this->assertSame([['sale.cashbox.handler.list', []]], $this->capturedCalls);
    }

    private function makeBatch(CoreInterface $core): Batch
    {
        return new Batch(new CashboxHandlerBatch($core, new NullLogger()), new NullLogger());
    }
}
