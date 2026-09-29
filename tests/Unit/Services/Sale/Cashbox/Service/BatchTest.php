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

namespace Bitrix24\SDK\Tests\Unit\Services\Sale\Cashbox\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Sale\Cashbox\Batch as CashboxBatch;
use Bitrix24\SDK\Services\Sale\Cashbox\Result\CashboxItemResult;
use Bitrix24\SDK\Services\Sale\Cashbox\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Services\Sale\BatchCoreStubTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
#[CoversClass(CashboxBatch::class)]
class BatchTest extends TestCase
{
    use BatchCoreStubTrait;

    #[Test]
    #[TestDox('add() sends cash register fields as top-level parameters')]
    public function testAddSendsTopLevelParameters(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): int => 21);

        $results = iterator_to_array($this->makeBatch($core)->add([['NAME' => 'Cashbox', 'REST_CODE' => 'code']]), false);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(AddedItemBatchResult::class, $results[0]);
        $this->assertSame(21, $results[0]->getId());
        $this->assertSame(['sale.cashbox.add', ['NAME' => 'Cashbox', 'REST_CODE' => 'code']], $this->capturedCalls[0]);
    }

    #[Test]
    #[TestDox('update() sends uppercase "ID" and "FIELDS" parameters')]
    public function testUpdateSendsUppercaseIdAndFields(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array($this->makeBatch($core)->update([21 => ['NAME' => 'New']]), false);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(UpdatedItemBatchResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccess());
        $this->assertSame(['sale.cashbox.update', ['ID' => '21', 'FIELDS' => ['NAME' => 'New']]], $this->capturedCalls[0]);
    }

    #[Test]
    #[TestDox('update() rejects non-integer identifiers')]
    public function testUpdateRejectsNonIntegerId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        iterator_to_array($this->makeBatch($core)->update(['abc' => ['NAME' => 'New']]));
    }

    #[Test]
    #[TestDox('delete() sends uppercase "ID" parameter')]
    public function testDeleteSendsUppercaseId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array($this->makeBatch($core)->delete([21]), false);

        $this->assertInstanceOf(DeletedItemBatchResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccess());
        $this->assertSame(['sale.cashbox.delete', ['ID' => '21']], $this->capturedCalls[0]);
    }

    #[Test]
    #[TestDox('list() sends uppercase parameters in a single call and respects limit')]
    public function testListSendsUppercaseParametersInSingleCall(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): array => [
            ['ID' => '1', 'NAME' => 'a'],
            ['ID' => '2', 'NAME' => 'b'],
            ['ID' => '3', 'NAME' => 'c'],
        ]);

        $items = iterator_to_array($this->makeBatch($core)->list(['ID', 'NAME'], ['ACTIVE' => 'Y'], ['ID' => 'ASC'], 2), false);

        $this->assertCount(2, $items);
        $this->assertInstanceOf(CashboxItemResult::class, $items[0]);
        $this->assertCount(1, $this->capturedCalls);
        $this->assertSame(
            ['sale.cashbox.list', ['SELECT' => ['ID', 'NAME'], 'FILTER' => ['ACTIVE' => 'Y'], 'ORDER' => ['ID' => 'ASC']]],
            $this->capturedCalls[0]
        );
    }

    private function makeBatch(CoreInterface $core): Batch
    {
        return new Batch(new CashboxBatch($core, new NullLogger()), new NullLogger());
    }
}
