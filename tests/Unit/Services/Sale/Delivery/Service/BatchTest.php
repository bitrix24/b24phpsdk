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

namespace Bitrix24\SDK\Tests\Unit\Services\Sale\Delivery\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Sale\Delivery\Batch as DeliveryBatch;
use Bitrix24\SDK\Services\Sale\Delivery\Result\DeliveryAddBatchResult;
use Bitrix24\SDK\Services\Sale\Delivery\Result\DeliveryItemResult;
use Bitrix24\SDK\Services\Sale\Delivery\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Services\Sale\BatchCoreStubTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
#[CoversClass(DeliveryBatch::class)]
class BatchTest extends TestCase
{
    use BatchCoreStubTrait;

    #[Test]
    #[TestDox('add() sends delivery fields as top-level parameters and returns parent id')]
    public function testAddSendsTopLevelParametersAndReturnsParentId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): array => [
            'parent' => ['ID' => 30, 'NAME' => $parameters['NAME']],
            'profiles' => [['ID' => 31], ['ID' => 32]],
        ]);

        $results = iterator_to_array(
            $this->makeBatch($core)->add([['REST_CODE' => 'code', 'NAME' => 'Delivery', 'CURRENCY' => 'USD']]),
            false
        );

        $this->assertInstanceOf(DeliveryAddBatchResult::class, $results[0]);
        $this->assertSame(30, $results[0]->getId());
        $this->assertSame('Delivery', $results[0]->getParent()->NAME);
        $this->assertCount(2, $results[0]->getProfiles());
        $this->assertSame(
            ['sale.delivery.add', ['REST_CODE' => 'code', 'NAME' => 'Delivery', 'CURRENCY' => 'USD']],
            $this->capturedCalls[0]
        );
    }

    #[Test]
    #[TestDox('update() sends uppercase "ID" with fields as top-level parameters')]
    public function testUpdateSendsUppercaseIdWithTopLevelFields(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array($this->makeBatch($core)->update([30 => ['NAME' => 'New', 'SORT' => 10]]), false);

        $this->assertInstanceOf(UpdatedItemBatchResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccess());
        $this->assertSame(['sale.delivery.update', ['ID' => '30', 'NAME' => 'New', 'SORT' => '10']], $this->capturedCalls[0]);
    }

    #[Test]
    #[TestDox('configUpdate() sends uppercase "ID" and "CONFIG" parameters')]
    public function testConfigUpdateSendsUppercaseIdAndConfig(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array(
            $this->makeBatch($core)->configUpdate([30 => [['CODE' => 'API_KEY', 'VALUE' => 'secret']]]),
            false
        );

        $this->assertInstanceOf(UpdatedItemBatchResult::class, $results[0]);
        $this->assertSame(
            ['sale.delivery.config.update', ['ID' => '30', 'CONFIG' => [['CODE' => 'API_KEY', 'VALUE' => 'secret']]]],
            $this->capturedCalls[0]
        );
    }

    #[Test]
    #[TestDox('delete() sends uppercase "ID" parameter')]
    public function testDeleteSendsUppercaseId(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): bool => true);

        $results = iterator_to_array($this->makeBatch($core)->delete([30]), false);

        $this->assertInstanceOf(DeletedItemBatchResult::class, $results[0]);
        $this->assertSame(['sale.delivery.delete', ['ID' => '30']], $this->capturedCalls[0]);
    }

    #[Test]
    #[TestDox('getlist() sends uppercase parameters in a single call')]
    public function testGetlistSendsUppercaseParametersInSingleCall(): void
    {
        $core = $this->makeCore(static fn (string $apiMethod, array $parameters): array => [
            ['ID' => 30, 'NAME' => 'a'],
            ['ID' => 31, 'NAME' => 'b'],
        ]);

        $items = iterator_to_array($this->makeBatch($core)->getlist(['ID', 'NAME'], ['@ID' => [30, 31]]), false);

        $this->assertCount(2, $items);
        $this->assertInstanceOf(DeliveryItemResult::class, $items[0]);
        $this->assertSame(
            [['sale.delivery.getlist', ['SELECT' => ['ID', 'NAME'], 'FILTER' => ['@ID' => [30, 31]]]]],
            $this->capturedCalls
        );
    }

    private function makeBatch(CoreInterface $core): Batch
    {
        return new Batch(new DeliveryBatch($core, new NullLogger()), new NullLogger());
    }
}
