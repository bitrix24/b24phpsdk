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
use Bitrix24\SDK\Services\Catalog\PriceType\Service\PriceType;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Service\RoundingRule;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoundingRule::class)]
class RoundingRuleTest extends TestCase
{
    private RoundingRule $roundingRuleService;

    private PriceType $priceTypeService;

    private int $catalogGroupId;

    #[\Override]
    protected function setUp(): void
    {
        $this->roundingRuleService = Factory::getServiceBuilder()->getCatalogScope()->roundingRule();
        $this->priceTypeService = Factory::getServiceBuilder()->getCatalogScope()->priceType();

        $basePriceTypes = $this->priceTypeService->list([], ['base' => 'Y'])->getPriceTypes();
        $this->catalogGroupId = $basePriceTypes[0]->id;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test RoundingRule::add, RoundingRule::get, RoundingRule::delete')]
    public function testAddGetDelete(): void
    {
        $addResult = $this->roundingRuleService->add([
            'catalogGroupId' => $this->catalogGroupId,
            'price' => 1000,
            'roundType' => 4,
            'roundPrecision' => 100,
        ]);
        $roundingRuleId = $addResult->roundingRule()->id;
        $this->assertSame($this->catalogGroupId, $addResult->roundingRule()->catalogGroupId);
        $this->assertSame(4, $addResult->roundingRule()->roundType);

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

        $updateResult = $this->roundingRuleService->update($roundingRuleId, [
            'catalogGroupId' => $this->catalogGroupId,
            'price' => 1500,
            'roundType' => 2,
            'roundPrecision' => 10,
        ]);
        $this->assertSame(2, $updateResult->roundingRule()->roundType);
        $this->assertSame(10.0, $updateResult->roundingRule()->roundPrecision);

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

        $listResult = $this->roundingRuleService->list([], ['id' => $roundingRuleId]);
        $this->assertCount(1, $listResult->getRoundingRules());

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
}
