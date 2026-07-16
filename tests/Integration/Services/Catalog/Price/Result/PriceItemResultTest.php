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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Price\Result;

use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Price\Result\PriceItemResult;
use Bitrix24\SDK\Services\Catalog\Price\Service\Price;
use Bitrix24\SDK\Services\Catalog\Product\Service\Product;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(PriceItemResult::class)]
class PriceItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Price $priceService;

    private Product $productService;

    private int $productId;

    private int $priceId;

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->priceService = $serviceBuilder->getCatalogScope()->price();
        $this->productService = $serviceBuilder->getCatalogScope()->product();

        $catalogService = $serviceBuilder->getCatalogScope()->catalog();
        $iblockId = $catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $this->productId = $this->productService->add([
            'iblockId' => $iblockId,
            'name' => sprintf('test product for price annotations %s', time()),
        ])->product()->id;

        $this->priceId = $this->priceService->add([
            'productId' => $this->productId,
            'catalogGroupId' => 1,
            'price' => 150.0,
            'currency' => 'USD',
        ])->price()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->priceService->delete($this->priceId);
        $this->productService->delete($this->productId);
    }

    #[Test]
    #[TestDox('all fields in PriceItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->priceService->get($this->priceId)->getCoreResponse()->getResponseData()->getResult()['price'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), PriceItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in PriceItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $priceItemResult = $this->priceService->get($this->priceId)->price();
        $this->assertIsInt($priceItemResult->id);
        $this->assertIsInt($priceItemResult->productId);
        $this->assertIsInt($priceItemResult->catalogGroupId);
        $this->assertIsFloat($priceItemResult->price);
        $this->assertIsString($priceItemResult->currency);
    }
}
