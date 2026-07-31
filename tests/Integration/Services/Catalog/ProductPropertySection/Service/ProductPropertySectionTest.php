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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductPropertySection\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\ProductPropertySectionDisplayType;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Service\ProductPropertySection;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductPropertySection::class)]
class ProductPropertySectionTest extends TestCase
{
    private ProductPropertySection $productPropertySectionService;

    private int $iblockId;

    private int $propertyId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductPropertySection::set and get')]
    public function testSetAndGet(): void
    {
        $fields = [
            'smartFilter' => 'Y',
            'displayType' => 'F',
            'displayExpanded' => 'N',
            'filterHint' => 'test hint',
        ];
        $setResult = $this->productPropertySectionService->set($this->propertyId, $fields)->productPropertySection();
        $this->assertEquals($this->propertyId, $setResult->propertyId);
        $this->assertTrue($setResult->smartFilter);
        $this->assertEquals(ProductPropertySectionDisplayType::checkboxes, $setResult->displayType);
        $this->assertFalse($setResult->displayExpanded);
        $this->assertEquals('test hint', $setResult->filterHint);

        $getResult = $this->productPropertySectionService->get($this->propertyId)->productPropertySection();
        $this->assertEquals($this->propertyId, $getResult->propertyId);
        $this->assertEquals('test hint', $getResult->filterHint);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductPropertySection::list')]
    public function testList(): void
    {
        $this->productPropertySectionService->set($this->propertyId, ['smartFilter' => 'Y']);
        $items = $this->productPropertySectionService
            ->list([], ['propertyId' => $this->propertyId], [])
            ->getProductPropertySections();
        $this->assertCount(1, $items);
        $this->assertEquals($this->propertyId, $items[0]->propertyId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->productPropertySectionService = Factory::getServiceBuilder()
            ->getCatalogScope()
            ->productPropertySection();

        $this->iblockId = Factory::getServiceBuilder()->getCatalogScope()->catalog()
            ->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $propertyAddResult = Factory::getCore()->call('catalog.productProperty.add', [
            'fields' => [
                'iblockId' => $this->iblockId,
                'name' => sprintf('test property %s', time()),
                'propertyType' => 'S',
                'code' => sprintf('TEST_PROP_%s', time()),
                'active' => 'Y',
            ],
        ]);
        $this->propertyId = (int)$propertyAddResult->getResponseData()->getResult()['productProperty']['id'];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        Factory::getCore()->call('catalog.productProperty.delete', ['id' => $this->propertyId]);
    }
}
