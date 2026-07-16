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
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Service\PriceTypeGroup;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(PriceTypeGroup::class)]
class PriceTypeGroupTest extends TestCase
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
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->priceTypeGroupService = $serviceBuilder->getCatalogScope()->priceTypeGroup();
        $this->priceTypeService = $serviceBuilder->getCatalogScope()->priceType();

        // a newly created price type gets auto-generated bindings for groupId=1 and groupId=2
        $this->priceTypeId = $this->priceTypeService->add([
            'name' => sprintf('test price type for group %s', time()),
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
    #[TestDox('test PriceTypeGroup::list, PriceTypeGroup::delete, PriceTypeGroup::add')]
    public function testListDeleteAdd(): void
    {
        $listResult = $this->priceTypeGroupService->list(
            [],
            ['catalogGroupId' => $this->priceTypeId, 'groupId' => 1, 'access' => 'N']
        );
        $bindings = $listResult->getPriceTypeGroups();
        $this->assertCount(1, $bindings);
        $bindingId = $bindings[0]->id;

        $this->assertTrue($this->priceTypeGroupService->delete($bindingId)->isSuccess());

        $addResult = $this->priceTypeGroupService->add([
            'catalogGroupId' => $this->priceTypeId,
            'groupId' => 1,
            'access' => 'N',
        ]);
        $this->assertSame($this->priceTypeId, $addResult->priceTypeGroup()->catalogGroupId);
        $this->assertSame(1, $addResult->priceTypeGroup()->groupId);
        $this->assertFalse($addResult->priceTypeGroup()->access);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test PriceTypeGroup::getFields')]
    public function testGetFields(): void
    {
        $this->assertIsArray($this->priceTypeGroupService->getFields()->getFieldsDescription());
    }
}
