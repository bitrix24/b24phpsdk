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

namespace Bitrix24\SDK\Tests\Unit\Services\Sale\PropertyRelation\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Services\Sale\PropertyRelation\Batch as PropertyRelationBatch;
use Bitrix24\SDK\Services\Sale\PropertyRelation\Result\PropertyRelationAddedBatchResult;
use Bitrix24\SDK\Services\Sale\PropertyRelation\Result\PropertyRelationItemResult;
use Bitrix24\SDK\Services\Sale\PropertyRelation\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Services\Sale\BatchCoreStubTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
#[CoversClass(PropertyRelationBatch::class)]
class BatchTest extends TestCase
{
    use BatchCoreStubTrait;

    #[Test]
    #[TestDox('add() wraps items into "fields" and returns bound entity id')]
    public function testAddWrapsFieldsAndReturnsEntityId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): array => [
            'propertyRelation' => $parameters['fields'],
        ]);

        $results = iterator_to_array(
            $this->makeBatch($core)->add([['entityId' => 9, 'entityType' => 'D', 'propertyId' => 15]]),
            false
        );

        $this->assertCount(1, $results);
        $this->assertInstanceOf(PropertyRelationAddedBatchResult::class, $results[0]);
        $this->assertSame(9, $results[0]->getId());
        $this->assertInstanceOf(PropertyRelationItemResult::class, $results[0]->propertyRelation());
        $this->assertSame(
            ['sale.propertyRelation.add', ['fields' => ['entityId' => '9', 'entityType' => 'D', 'propertyId' => '15']]],
            $this->capturedCalls[0]
        );
    }

    #[Test]
    #[TestDox('list() unwraps "propertyRelations" result key and paginates with "start" parameter')]
    public function testListPaginatesWithStart(): void
    {
        $core = $this->makeCore(
            static function (string $apiMethod, array $parameters): array {
                $start = (int)$parameters['start'];

                return [
                    'propertyRelations' => array_map(
                        static fn (int $number): array => ['entityId' => $number, 'entityType' => 'D', 'propertyId' => 15],
                        range($start + 1, min($start + 50, 51))
                    ),
                ];
            },
            51
        );

        $items = iterator_to_array($this->makeBatch($core)->list([], ['propertyId' => 15]), false);

        $this->assertCount(51, $items);
        $this->assertInstanceOf(PropertyRelationItemResult::class, $items[50]);
        $this->assertSame('sale.propertyRelation.list', $this->capturedCalls[0][0]);
        $this->assertSame('50', $this->capturedCalls[2][1]['start']);
    }

    private function makeBatch(CoreInterface $core): Batch
    {
        return new Batch(new PropertyRelationBatch($core, new NullLogger()), new NullLogger());
    }
}
