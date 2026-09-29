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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\PriceTypeLang\Result;

use Bitrix24\SDK\Services\Catalog\PriceType\Service\PriceType;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Result\PriceTypeLangItemResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Service\PriceTypeLang;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(PriceTypeLangItemResult::class)]
class PriceTypeLangItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private PriceTypeLang $priceTypeLangService;

    private PriceType $priceTypeService;

    private int $priceTypeId;

    private int $priceTypeLangId;

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->priceTypeLangService = $serviceBuilder->getCatalogScope()->priceTypeLang();
        $this->priceTypeService = $serviceBuilder->getCatalogScope()->priceType();

        $this->priceTypeId = $this->priceTypeService->add([
            'name' => sprintf('test price type for lang annotations %s', time()),
            'sort' => 50,
        ])->priceType()->id;

        $this->priceTypeLangId = $this->priceTypeLangService->add([
            'catalogGroupId' => $this->priceTypeId,
            'lang' => 'kz',
            'name' => 'PRICE',
        ])->priceTypeLang()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->priceTypeLangService->delete($this->priceTypeLangId);
        $this->priceTypeService->delete($this->priceTypeId);
    }

    #[Test]
    #[TestDox('all fields in PriceTypeLangItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->priceTypeLangService->get($this->priceTypeLangId)
            ->getCoreResponse()->getResponseData()->getResult()['priceTypeLang'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), PriceTypeLangItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in PriceTypeLangItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $priceTypeLangItemResult = $this->priceTypeLangService->get($this->priceTypeLangId)->priceTypeLang();
        $this->assertIsInt($priceTypeLangItemResult->id);
        $this->assertIsInt($priceTypeLangItemResult->catalogGroupId);
        $this->assertIsString($priceTypeLangItemResult->lang);
        $this->assertIsString($priceTypeLangItemResult->name);
    }
}
