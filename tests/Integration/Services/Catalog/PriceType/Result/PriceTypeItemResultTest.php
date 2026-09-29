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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\PriceType\Result;

use Bitrix24\SDK\Services\Catalog\PriceType\Result\PriceTypeItemResult;
use Bitrix24\SDK\Services\Catalog\PriceType\Service\PriceType;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(PriceTypeItemResult::class)]
class PriceTypeItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private PriceType $priceTypeService;

    private int $priceTypeId;

    #[\Override]
    protected function setUp(): void
    {
        $this->priceTypeService = Fabric::getServiceBuilder()->getCatalogScope()->priceType();
        $this->priceTypeId = $this->priceTypeService->add([
            'name' => sprintf('test price type annotations %s', time()),
            'sort' => 50,
        ])->priceType()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->priceTypeService->delete($this->priceTypeId);
    }

    #[Test]
    #[TestDox('all fields in PriceTypeItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->priceTypeService->get($this->priceTypeId)->getCoreResponse()->getResponseData()->getResult()['priceType'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), PriceTypeItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in PriceTypeItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $priceTypeItemResult = $this->priceTypeService->get($this->priceTypeId)->priceType();
        $this->assertIsInt($priceTypeItemResult->id);
        $this->assertIsString($priceTypeItemResult->name);
        $this->assertIsBool($priceTypeItemResult->base);
        $this->assertIsInt($priceTypeItemResult->sort);
    }
}
