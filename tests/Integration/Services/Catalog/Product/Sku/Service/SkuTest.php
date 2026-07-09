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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Product\Sku\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Product\Sku\Service\Sku;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Sku::class)]
class SkuTest extends TestCase
{
    private Sku $skuService;

    private Catalog $catalogService;

    #[\Override]
    protected function setUp(): void
    {
        $this->skuService = Factory::getServiceBuilder()->getCatalogScope()->productSku();
        $this->catalogService = Factory::getServiceBuilder()->getCatalogScope()->catalog();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Sku::add, get, update, delete, list, fieldsByFilter')]
    public function testAddGetUpdateDeleteListFieldsByFilter(): void
    {
        $iblockId = $this->catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $addResult = $this->skuService->add([
            'iblockId' => $iblockId,
            'name' => sprintf('test sku %s', time()),
        ]);
        $skuId = $addResult->sku()->id;
        $this->assertGreaterThan(0, $skuId);

        $getResult = $this->skuService->get($skuId);
        $this->assertEquals($skuId, $getResult->sku()->id);

        $updated = $this->skuService->update($skuId, ['name' => 'updated sku name']);
        $this->assertEquals('updated sku name', $updated->sku()->name);

        $listResult = $this->skuService->list(
            ['id', 'iblockId'],
            ['id' => $skuId, 'iblockId' => $iblockId]
        );
        $this->assertCount(1, $listResult->getSkus());

        $fields = $this->skuService->fieldsByFilter($iblockId);
        $this->assertIsArray($fields->getFieldsDescription());

        $this->assertTrue($this->skuService->delete($skuId)->isSuccess());
    }
}
