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
use Bitrix24\SDK\Services\Catalog\RoundingRule\Result\RoundingRuleItemResult;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Service\RoundingRule;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoundingRule::class)]
class RoundingRuleTest extends TestCase
{
    use CustomBitrix24Assertions;

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
    #[TestDox('test RoundingRule::add, RoundingRule::get, RoundingRule::delete')]
    public function testAddGetDelete(): void
    {
        $roundingRuleResult = $this->roundingRuleService->add([
            'catalogGroupId' => $this->catalogGroupId,
            'price' => 1000,
            'roundType' => 4,
            'roundPrecision' => 100,
        ]);
        $roundingRuleId = $roundingRuleResult->roundingRule()->id;
        $this->assertSame($this->catalogGroupId, $roundingRuleResult->roundingRule()->catalogGroupId);
        $this->assertSame(4, $roundingRuleResult->roundingRule()->roundType);

        $getResult = $this->roundingRuleService->get($roundingRuleId);
        $this->assertSame($roundingRuleId, $getResult->roundingRule()->id);

        $this->assertTrue($this->roundingRuleService->delete($roundingRuleId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test RoundingRule::update')]
    public function testUpdate(): void
    {
        $roundingRuleId = $this->roundingRuleService->add([
            'catalogGroupId' => $this->catalogGroupId,
            'price' => 1000,
            'roundType' => 4,
            'roundPrecision' => 100,
        ])->roundingRule()->id;

        $roundingRuleResult = $this->roundingRuleService->update($roundingRuleId, [
            'catalogGroupId' => $this->catalogGroupId,
            'price' => 1500,
            'roundType' => 2,
            'roundPrecision' => 10,
        ]);
        $this->assertSame(2, $roundingRuleResult->roundingRule()->roundType);
        $this->assertSame(10.0, $roundingRuleResult->roundingRule()->roundPrecision);

        $this->roundingRuleService->delete($roundingRuleId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test RoundingRule::list')]
    public function testList(): void
    {
        $roundingRuleId = $this->roundingRuleService->add([
            'catalogGroupId' => $this->catalogGroupId,
            'price' => 1000,
            'roundType' => 4,
            'roundPrecision' => 100,
        ])->roundingRule()->id;

        $roundingRulesResult = $this->roundingRuleService->list([], ['id' => $roundingRuleId]);
        $this->assertCount(1, $roundingRulesResult->getRoundingRules());

        $this->roundingRuleService->delete($roundingRuleId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test RoundingRule::getFields')]
    public function testGetFields(): void
    {
        $this->assertIsArray($this->roundingRuleService->getFields()->getFieldsDescription());
    }

    #[TestDox('all fields in RoundingRuleItemResult are annotated in phpdoc')]
    public function testAllFieldsAnnotated(): void
    {
        $fields = $this->roundingRuleService->getFields()->getFieldsDescription();
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), RoundingRuleItemResult::class);
    }

    #[TestDox('all fields in RoundingRuleItemResult have valid type annotation')]
    public function testAllFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->roundingRuleService->getFields()->getFieldsDescription();
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, RoundingRuleItemResult::class);
    }
}
