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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\PriceType\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\PriceType\Service\PriceType;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(PriceType::class)]
class PriceTypeTest extends TestCase
{
    private PriceType $priceTypeService;

    #[\Override]
    protected function setUp(): void
    {
        $this->priceTypeService = Factory::getServiceBuilder()->getCatalogScope()->priceType();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test PriceType::add, PriceType::get, PriceType::delete')]
    public function testAddGetDelete(): void
    {
        $name = sprintf('test price type %s', time());
        $addResult = $this->priceTypeService->add([
            'name' => $name,
            'base' => 'N',
            'sort' => 50,
            'xmlId' => sprintf('test-price-type-%s', time()),
        ]);
        $priceTypeId = $addResult->priceType()->id;
        $this->assertSame($name, $addResult->priceType()->name);
        $this->assertFalse($addResult->priceType()->base);

        $getResult = $this->priceTypeService->get($priceTypeId);
        $this->assertSame($priceTypeId, $getResult->priceType()->id);

        $this->assertTrue($this->priceTypeService->delete($priceTypeId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test PriceType::update')]
    public function testUpdate(): void
    {
        $priceTypeId = $this->priceTypeService->add([
            'name' => sprintf('test price type %s', time()),
            'sort' => 50,
        ])->priceType()->id;

        $updatedName = sprintf('updated price type %s', time());
        $updateResult = $this->priceTypeService->update($priceTypeId, ['name' => $updatedName]);
        $this->assertSame($updatedName, $updateResult->priceType()->name);

        $this->priceTypeService->delete($priceTypeId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test PriceType::list')]
    public function testList(): void
    {
        $priceTypeId = $this->priceTypeService->add([
            'name' => sprintf('test price type %s', time()),
            'sort' => 50,
        ])->priceType()->id;

        $listResult = $this->priceTypeService->list([], ['id' => $priceTypeId]);
        $this->assertCount(1, $listResult->getPriceTypes());

        $this->priceTypeService->delete($priceTypeId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test PriceType::getFields')]
    public function testGetFields(): void
    {
        $this->assertIsArray($this->priceTypeService->getFields()->getFieldsDescription());
    }
}
