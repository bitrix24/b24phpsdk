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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\PriceTypeLang\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\PriceType\Service\PriceType;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Service\PriceTypeLang;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(PriceTypeLang::class)]
class PriceTypeLangTest extends TestCase
{
    private PriceTypeLang $priceTypeLangService;

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
        $this->priceTypeLangService = $serviceBuilder->getCatalogScope()->priceTypeLang();
        $this->priceTypeService = $serviceBuilder->getCatalogScope()->priceType();

        $this->priceTypeId = $this->priceTypeService->add([
            'name' => sprintf('test price type for lang %s', time()),
            'sort' => 50,
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
    #[TestDox('test PriceTypeLang::add, PriceTypeLang::get, PriceTypeLang::delete')]
    public function testAddGetDelete(): void
    {
        $addResult = $this->priceTypeLangService->add([
            'catalogGroupId' => $this->priceTypeId,
            'lang' => 'kz',
            'name' => 'PRICE',
        ]);
        $langId = $addResult->priceTypeLang()->id;
        $this->assertSame('PRICE', $addResult->priceTypeLang()->name);
        $this->assertSame('kz', $addResult->priceTypeLang()->lang);

        $getResult = $this->priceTypeLangService->get($langId);
        $this->assertSame($langId, $getResult->priceTypeLang()->id);

        $this->assertTrue($this->priceTypeLangService->delete($langId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test PriceTypeLang::update')]
    public function testUpdate(): void
    {
        $langId = $this->priceTypeLangService->add([
            'catalogGroupId' => $this->priceTypeId,
            'lang' => 'kz',
            'name' => 'PRICE',
        ])->priceTypeLang()->id;

        $updateResult = $this->priceTypeLangService->update($langId, ['name' => 'Base Price']);
        $this->assertSame('Base Price', $updateResult->priceTypeLang()->name);

        $this->priceTypeLangService->delete($langId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test PriceTypeLang::list')]
    public function testList(): void
    {
        $langId = $this->priceTypeLangService->add([
            'catalogGroupId' => $this->priceTypeId,
            'lang' => 'kz',
            'name' => 'PRICE',
        ])->priceTypeLang()->id;

        $listResult = $this->priceTypeLangService->list([], ['catalogGroupId' => $this->priceTypeId]);
        $this->assertCount(1, $listResult->getPriceTypeLangs());

        $this->priceTypeLangService->delete($langId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test PriceTypeLang::getLanguages')]
    public function testGetLanguages(): void
    {
        $languages = $this->priceTypeLangService->getLanguages()->getLanguages();
        $this->assertNotEmpty($languages);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test PriceTypeLang::getFields')]
    public function testGetFields(): void
    {
        $this->assertIsArray($this->priceTypeLangService->getFields()->getFieldsDescription());
    }
}
