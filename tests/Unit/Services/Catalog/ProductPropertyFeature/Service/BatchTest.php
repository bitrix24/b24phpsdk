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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\ProductPropertyFeature\Service;

use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureItemResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureUpdatedBatchResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    #[Test]
    #[TestDox('list() yields ProductPropertyFeatureItemResult items')]
    public function testListYieldsItemResults(): void
    {
        $batch = new Batch(new NullBatch(), new NullLogger());

        foreach ($batch->list() as $item) {
            $this->assertInstanceOf(ProductPropertyFeatureItemResult::class, $item);
        }

        $this->addToAssertionCount(1);
    }

    #[Test]
    #[TestDox('add() yields ProductPropertyFeatureAddedBatchResult items and forwards fields wrapper')]
    public function testAddYieldsAddedBatchResults(): void
    {
        $batchOperations = $this->createMock(BatchOperationsInterface::class);
        $batchOperations->expects($this->once())
            ->method('addEntityItems')
            ->with(
                'catalog.productPropertyFeature.add',
                [
                    [
                        'fields' => [
                            'propertyId' => 901,
                            'moduleId' => 'iblock',
                            'featureId' => 'LIST_PAGE_SHOW',
                            'isEnabled' => 'Y',
                        ],
                    ],
                ]
            )
            ->willReturn((static function (): \Generator {
                yield from [];
            })());

        $batch = new Batch($batchOperations, new NullLogger());
        foreach ($batch->add([
            [
                'propertyId' => 901,
                'moduleId' => 'iblock',
                'featureId' => 'LIST_PAGE_SHOW',
                'isEnabled' => 'Y',
            ],
        ]) as $item) {
            $this->assertInstanceOf(ProductPropertyFeatureAddedBatchResult::class, $item);
        }

        $this->addToAssertionCount(1);
    }

    #[Test]
    #[TestDox('update() yields ProductPropertyFeatureUpdatedBatchResult items')]
    public function testUpdateYieldsUpdatedBatchResults(): void
    {
        $batch = new Batch(new NullBatch(), new NullLogger());

        foreach ($batch->update([101 => ['fields' => ['isEnabled' => 'N']]]) as $item) {
            $this->assertInstanceOf(ProductPropertyFeatureUpdatedBatchResult::class, $item);
        }

        $this->addToAssertionCount(1);
    }
}
