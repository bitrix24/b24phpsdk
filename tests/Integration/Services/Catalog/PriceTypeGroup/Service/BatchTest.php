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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\PriceTypeGroup\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\PriceType\Service\PriceType;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Service\Batch;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Service\PriceTypeGroup;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private PriceTypeGroup $priceTypeGroupService;

    private PriceType $priceTypeService;

    private int $priceTypeId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->priceTypeGroupService = $serviceBuilder->getCatalogScope()->priceTypeGroup();
        $this->priceTypeService = $serviceBuilder->getCatalogScope()->priceType();

        $this->priceTypeId = $this->priceTypeService->add([
            'name' => sprintf('test price type for batch group %s', time()),
            'sort' => 90,
        ])->priceType()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->priceTypeService->delete($this->priceTypeId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Batch::delete')]
    public function testDelete(): void
    {
        $listResult = $this->priceTypeGroupService->list(
            [],
            ['catalogGroupId' => $this->priceTypeId]
        );
        $bindingIds = array_map(
            static fn ($binding): int => $binding->id,
            $listResult->getPriceTypeGroups()
        );
        $this->assertNotEmpty($bindingIds);

        $deletedCount = 0;
        foreach ($this->priceTypeGroupService->batch->delete($bindingIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(count($bindingIds), $deletedCount);
    }
}
