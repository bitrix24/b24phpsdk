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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Product\ProductService\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Service\ProductService;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductService::class)]
class ProductServiceTest extends TestCase
{
    private ProductService $productServiceScope;

    private Catalog $catalogService;

    #[\Override]
    protected function setUp(): void
    {
        $this->productServiceScope = Fabric::getServiceBuilder()->getCatalogScope()->productService();
        $this->catalogService = Fabric::getServiceBuilder()->getCatalogScope()->catalog();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductService::add, get, update, delete, list, fieldsByFilter')]
    public function testAddGetUpdateDeleteListFieldsByFilter(): void
    {
        $iblockId = $this->catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $productServiceResult = $this->productServiceScope->add([
            'iblockId' => $iblockId,
            'name' => sprintf('test service %s', time()),
        ]);
        $serviceId = $productServiceResult->productService()->id;
        $this->assertGreaterThan(0, $serviceId);

        $getResult = $this->productServiceScope->get($serviceId);
        $this->assertEquals($serviceId, $getResult->productService()->id);

        $updated = $this->productServiceScope->update($serviceId, ['name' => 'updated service name']);
        $this->assertEquals('updated service name', $updated->productService()->name);

        $productServicesResult = $this->productServiceScope->list(
            ['id', 'iblockId'],
            ['id' => $serviceId, 'iblockId' => $iblockId]
        );
        $this->assertCount(1, $productServicesResult->getProductServices());

        $fieldsResult = $this->productServiceScope->fieldsByFilter($iblockId);
        $this->assertIsArray($fieldsResult->getFieldsDescription());

        $this->assertTrue($this->productServiceScope->delete($serviceId)->isSuccess());
    }
}
