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

namespace Bitrix24\SDK\Tests\Unit\Services\Sale\PersonTypeStatus\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Batch as PersonTypeStatusBatch;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Result\PersonTypeStatusAddBatchResult;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Result\PersonTypeStatusItemResult;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Services\Sale\BatchCoreStubTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
#[CoversClass(PersonTypeStatusBatch::class)]
class BatchTest extends TestCase
{
    use BatchCoreStubTrait;

    #[Test]
    #[TestDox('add() wraps items into "fields" and returns added record')]
    public function testAddWrapsFields(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): array => [
            'businessValuePersonDomain' => $parameters['fields'],
        ]);

        $results = iterator_to_array(
            $this->makeBatch($core)->add([['personTypeId' => 3, 'domain' => 'I'], ['personTypeId' => 4, 'domain' => 'E']]),
            false
        );

        $this->assertCount(2, $results);
        $this->assertInstanceOf(PersonTypeStatusAddBatchResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccess());
        $this->assertSame('E', $results[1]->personTypeStatus()->domain);
        $this->assertSame(
            ['sale.businessValuePersonDomain.add', ['fields' => ['personTypeId' => '3', 'domain' => 'I']]],
            $this->capturedCalls[0]
        );
    }

    #[Test]
    #[TestDox('list() unwraps "businessValuePersonDomains" result key and paginates with "start" parameter')]
    public function testListPaginatesWithStart(): void
    {
        $core = $this->makeCore(
            static function (string $apiMethod, array $parameters): array {
                $start = (int)$parameters['start'];

                return [
                    'businessValuePersonDomains' => array_map(
                        static fn (int $number): array => ['personTypeId' => $number, 'domain' => 'I'],
                        range($start + 1, min($start + 50, 55))
                    ),
                ];
            },
            55
        );

        $items = iterator_to_array($this->makeBatch($core)->list(['domain' => 'I']), false);

        $this->assertCount(55, $items);
        $this->assertInstanceOf(PersonTypeStatusItemResult::class, $items[0]);
        $this->assertSame('sale.businessValuePersonDomain.list', $this->capturedCalls[0][0]);
        $this->assertSame(['domain' => 'I'], $this->capturedCalls[0][1]['filter']);
        $this->assertSame('50', $this->capturedCalls[2][1]['start']);
    }

    private function makeBatch(CoreInterface $core): Batch
    {
        return new Batch(new PersonTypeStatusBatch($core, new NullLogger()), new NullLogger());
    }
}
