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

namespace Bitrix24\SDK\Tests\Unit\Services\Sale\StatusLang\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Services\Sale\StatusLang\Batch as StatusLangBatch;
use Bitrix24\SDK\Services\Sale\StatusLang\Result\StatusLangAddBatchResult;
use Bitrix24\SDK\Services\Sale\StatusLang\Result\StatusLangItemResult;
use Bitrix24\SDK\Services\Sale\StatusLang\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Services\Sale\BatchCoreStubTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
#[CoversClass(StatusLangBatch::class)]
class BatchTest extends TestCase
{
    use BatchCoreStubTrait;

    #[Test]
    #[TestDox('add() wraps items into "fields" and returns added status localization')]
    public function testAddWrapsFields(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): array => [
            'statusLang' => $parameters['fields'],
        ]);

        $results = iterator_to_array(
            $this->makeBatch($core)->add([['statusId' => 'XA', 'lid' => 'en', 'name' => 'Test']]),
            false
        );

        $this->assertCount(1, $results);
        $this->assertInstanceOf(StatusLangAddBatchResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccess());
        $this->assertSame('en', $results[0]->statusLang()->lid);
        $this->assertSame(
            ['sale.statusLang.add', ['fields' => ['statusId' => 'XA', 'lid' => 'en', 'name' => 'Test']]],
            $this->capturedCalls[0]
        );
    }

    #[Test]
    #[TestDox('list() unwraps "statusLangs" result key and paginates with "start" parameter')]
    public function testListPaginatesWithStart(): void
    {
        $core = $this->makeCore(
            static function (string $apiMethod, array $parameters): array {
                $start = (int)$parameters['start'];

                return [
                    'statusLangs' => array_map(
                        static fn (int $number): array => ['statusId' => 'S' . $number, 'lid' => 'en'],
                        range($start + 1, min($start + 50, 70))
                    ),
                ];
            },
            70
        );

        $items = iterator_to_array($this->makeBatch($core)->list([], ['lid' => 'en'], [], 60), false);

        $this->assertCount(60, $items);
        $this->assertInstanceOf(StatusLangItemResult::class, $items[0]);
        $this->assertSame('S60', $items[59]->statusId);
        $this->assertSame('sale.statusLang.list', $this->capturedCalls[0][0]);
        $this->assertSame('50', $this->capturedCalls[2][1]['start']);
    }

    private function makeBatch(CoreInterface $core): Batch
    {
        return new Batch(new StatusLangBatch($core, new NullLogger()), new NullLogger());
    }
}
