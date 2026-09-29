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

namespace Bitrix24\SDK\Tests\Unit\Services\Sale\ShipmentPropertyValue\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Batch as ShipmentPropertyValueBatch;
use Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Result\ShipmentPropertyValueItemResult;
use Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Services\Sale\BatchCoreStubTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
#[CoversClass(ShipmentPropertyValueBatch::class)]
class BatchTest extends TestCase
{
    use BatchCoreStubTrait;

    #[Test]
    #[TestDox('delete() sends lowercase "id" parameter')]
    public function testDeleteSendsLowercaseId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array($this->makeBatch($core)->delete([7, 8]), false);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(DeletedItemBatchResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccess());
        $this->assertSame(['sale.shipmentpropertyvalue.delete', ['id' => '7']], $this->capturedCalls[0]);
        $this->assertSame(['sale.shipmentpropertyvalue.delete', ['id' => '8']], $this->capturedCalls[1]);
    }

    #[Test]
    #[TestDox('delete() rejects non-integer identifiers')]
    public function testDeleteRejectsNonIntegerId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        iterator_to_array($this->makeBatch($core)->delete(['7']));
    }

    #[Test]
    #[TestDox('list() unwraps "propertyValues" result key and chains batch queries by the last "id"')]
    public function testListChainsBatchQueriesByLastId(): void
    {
        $core = $this->makeCore(
            static function (string $apiMethod, array $parameters): array {
                $lastId = $parameters['filter']['>id'] ?? '0';
                $firstId = str_starts_with((string)$lastId, '$result') ? 101 : (int)$lastId + 1;

                return [
                    'propertyValues' => array_map(
                        static fn (int $id): array => ['id' => $id],
                        range($firstId, min($firstId + 49, 120))
                    ),
                ];
            },
            120
        );

        $ids = [];
        foreach ($this->makeBatch($core)->list(['value'], ['shipmentId' => 1]) as $item) {
            $this->assertInstanceOf(ShipmentPropertyValueItemResult::class, $item);
            $ids[] = $item->id;
        }

        $this->assertSame(range(1, 120), $ids);
        $this->assertSame(['value', 'id'], $this->capturedCalls[0][1]['select']);
        $this->assertSame(['shipmentId' => '1', '>id' => '$result[cmd_0][propertyValues][49][id]'], $this->capturedCalls[2][1]['filter']);
    }

    private function makeBatch(CoreInterface $core): Batch
    {
        return new Batch(new ShipmentPropertyValueBatch($core, new NullLogger()), new NullLogger());
    }
}
