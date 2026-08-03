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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Product\Offer\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Product\Offer\Service\Offer;
use Bitrix24\SDK\Services\Catalog\Product\Sku\Service\Sku;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Offer::class)]
class OfferTest extends TestCase
{
    private Offer $offerService;

    private Sku $skuService;

    private Catalog $catalogService;

    #[\Override]
    protected function setUp(): void
    {
        $this->offerService = Factory::getServiceBuilder()->getCatalogScope()->productOffer();
        $this->skuService = Factory::getServiceBuilder()->getCatalogScope()->productSku();
        $this->catalogService = Factory::getServiceBuilder()->getCatalogScope()->catalog();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Offer::add, get, update, delete, list, fieldsByFilter')]
    public function testAddGetUpdateDeleteListFieldsByFilter(): void
    {
        $catalogs = $this->catalogService->list([], [], [], 1)->getCatalogs();
        $productCatalog = null;
        $offersCatalog = null;
        foreach ($catalogs as $catalog) {
            if ($catalog->productIblockId === null) {
                $productCatalog = $catalog;
            } else {
                $offersCatalog = $catalog;
            }
        }

        $this->assertNotNull($productCatalog, 'products catalog not found');
        $this->assertNotNull($offersCatalog, 'offers catalog not found');

        $skuId = $this->skuService->add([
            'iblockId' => $productCatalog->iblockId,
            'name' => sprintf('test sku for offer %s', time()),
        ])->sku()->id;

        $addResult = $this->offerService->add([
            'iblockId' => $offersCatalog->iblockId,
            'name' => sprintf('test offer %s', time()),
            'parentId' => $skuId,
        ]);
        $offerId = $addResult->offer()->id;
        $this->assertGreaterThan(0, $offerId);

        $getResult = $this->offerService->get($offerId);
        $this->assertEquals($offerId, $getResult->offer()->id);

        $updated = $this->offerService->update($offerId, ['name' => 'updated offer name']);
        $this->assertEquals('updated offer name', $updated->offer()->name);

        $listResult = $this->offerService->list(
            ['id', 'iblockId'],
            ['id' => $offerId, 'iblockId' => $offersCatalog->iblockId]
        );
        $this->assertCount(1, $listResult->getOffers());

        $fields = $this->offerService->fieldsByFilter($offersCatalog->iblockId);
        $this->assertIsArray($fields->getFieldsDescription());

        $this->assertTrue($this->offerService->delete($offerId)->isSuccess());
        $this->assertTrue($this->skuService->delete($skuId)->isSuccess());
    }
}
