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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\PriceTypeGroup\Result;

use Bitrix24\SDK\Services\Catalog\PriceType\Service\PriceType;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Result\PriceTypeGroupItemResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Service\PriceTypeGroup;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(PriceTypeGroupItemResult::class)]
class PriceTypeGroupItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private PriceTypeGroup $priceTypeGroupService;

    private PriceType $priceTypeService;

    private int $priceTypeId;

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->priceTypeGroupService = $serviceBuilder->getCatalogScope()->priceTypeGroup();
        $this->priceTypeService = $serviceBuilder->getCatalogScope()->priceType();

        $this->priceTypeId = $this->priceTypeService->add([
            'name' => sprintf('test price type for group annotations %s', time()),
            'sort' => 90,
        ])->priceType()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->priceTypeService->delete($this->priceTypeId);
    }

    #[Test]
    #[TestDox('all fields in PriceTypeGroupItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItems = $this->priceTypeGroupService->list([], ['catalogGroupId' => $this->priceTypeId])
            ->getCoreResponse()->getResponseData()->getResult()['priceTypeGroups'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItems[0]), PriceTypeGroupItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in PriceTypeGroupItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $bindings = $this->priceTypeGroupService->list([], ['catalogGroupId' => $this->priceTypeId])->getPriceTypeGroups();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($bindings[0], PriceTypeGroupItemResult::class);
    }
}
