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

namespace Bitrix24\SDK\Tests\Unit\Services\Sale\TradePlatform\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Services\Sale\TradePlatform\Batch as TradePlatformBatch;
use Bitrix24\SDK\Services\Sale\TradePlatform\Result\TradePlatformItemResult;
use Bitrix24\SDK\Services\Sale\TradePlatform\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Services\Sale\BatchCoreStubTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
#[CoversClass(TradePlatformBatch::class)]
class BatchTest extends TestCase
{
    use BatchCoreStubTrait;

    #[Test]
    #[TestDox('list() unwraps "tradePlatforms" result key and respects limit')]
    public function testListUnwrapsResultKeyAndRespectsLimit(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): array => [
            'tradePlatforms' => [['id' => 1, 'code' => 'a'], ['id' => 2, 'code' => 'b'], ['id' => 3, 'code' => 'c']],
        ]);

        $items = iterator_to_array($this->makeBatch($core)->list(['code'], [], [], 2), false);

        $this->assertCount(2, $items);
        $this->assertInstanceOf(TradePlatformItemResult::class, $items[0]);
        $this->assertSame('b', $items[1]->code);
        $this->assertSame('sale.tradeplatform.list', $this->capturedCalls[0][0]);
        $this->assertSame(['code', 'id'], $this->capturedCalls[0][1]['select']);
    }

    #[Test]
    #[TestDox('list() chains batch queries by the last "id" in the "tradePlatforms" result key')]
    public function testListChainsBatchQueriesByLastId(): void
    {
        $core = $this->makeCore(
            static function (string $apiMethod, array $parameters): array {
                $lastId = $parameters['filter']['>id'] ?? '0';
                $firstId = str_starts_with((string)$lastId, '$result') ? 101 : (int)$lastId + 1;

                return [
                    'tradePlatforms' => array_map(
                        static fn (int $id): array => ['id' => $id],
                        range($firstId, min($firstId + 49, 120))
                    ),
                ];
            },
            120
        );

        $ids = [];
        foreach ($this->makeBatch($core)->list() as $item) {
            $ids[] = $item->id;
        }

        $this->assertSame(range(1, 120), $ids);
        $this->assertSame(['>id' => '$result[cmd_0][tradePlatforms][49][id]'], $this->capturedCalls[2][1]['filter']);
    }

    private function makeBatch(CoreInterface $core): Batch
    {
        return new Batch(new TradePlatformBatch($core, new NullLogger()), new NullLogger());
    }
}
