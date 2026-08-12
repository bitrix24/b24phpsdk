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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\RoundingRule\Result;

use Bitrix24\SDK\Services\Catalog\RoundingRule\Result\RoundingRuleItemResult;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Service\RoundingRule;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoundingRuleItemResult::class)]
class RoundingRuleItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private RoundingRule $roundingRuleService;

    private int $roundingRuleId;

    #[\Override]
    protected function setUp(): void
    {
        $this->roundingRuleService = Factory::getServiceBuilder()->getCatalogScope()->roundingRule();

        $priceTypeService = Factory::getServiceBuilder()->getCatalogScope()->priceType();
        $basePriceTypes = $priceTypeService->list([], ['base' => 'Y'])->getPriceTypes();
        $catalogGroupId = $basePriceTypes[0]->id;

        $this->roundingRuleId = $this->roundingRuleService->add([
            'catalogGroupId' => $catalogGroupId,
            'price' => 1000,
            'roundType' => 4,
            'roundPrecision' => 100,
        ])->roundingRule()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->roundingRuleService->delete($this->roundingRuleId);
    }

    #[Test]
    #[TestDox('all fields in RoundingRuleItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->roundingRuleService->get($this->roundingRuleId)->getCoreResponse()->getResponseData()->getResult()['roundingRule'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), RoundingRuleItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in RoundingRuleItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $roundingRuleItemResult = $this->roundingRuleService->get($this->roundingRuleId)->roundingRule();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($roundingRuleItemResult, RoundingRuleItemResult::class);
    }
}
