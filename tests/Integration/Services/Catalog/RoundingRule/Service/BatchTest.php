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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\RoundingRule\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Service\Batch;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Service\RoundingRule;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    // default «Base» price type identifier, always present on a Bitrix24 portal
    private const BASE_CATALOG_GROUP_ID = 1;

    private RoundingRule $roundingRuleService;

    private int $catalogGroupId;

    #[\Override]
    protected function setUp(): void
    {
        $this->roundingRuleService = Fabric::getServiceBuilder()->getCatalogScope()->roundingRule();
        $this->catalogGroupId = self::BASE_CATALOG_GROUP_ID;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Batch::add, Batch::update, Batch::delete')]
    public function testAddUpdateDelete(): void
    {
        $addedIds = [];
        foreach ($this->roundingRuleService->batch->add([
            ['catalogGroupId' => $this->catalogGroupId, 'price' => 1000, 'roundType' => 4, 'roundPrecision' => 100],
        ]) as $addedItemResult) {
            $addedIds[] = $addedItemResult->roundingRule()->id;
        }

        $this->assertCount(1, $addedIds);

        $updatePayload = [];
        foreach ($addedIds as $addedId) {
            $updatePayload[$addedId] = ['catalogGroupId' => $this->catalogGroupId, 'price' => 1500, 'roundType' => 2, 'roundPrecision' => 10];
        }

        $updatedCount = 0;
        foreach ($this->roundingRuleService->batch->update($updatePayload) as $updatedItemResult) {
            $this->assertSame(2, $updatedItemResult->roundingRule()->roundType);
            $updatedCount++;
        }

        $this->assertSame(1, $updatedCount);

        $deletedCount = 0;
        foreach ($this->roundingRuleService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(1, $deletedCount);
    }
}
