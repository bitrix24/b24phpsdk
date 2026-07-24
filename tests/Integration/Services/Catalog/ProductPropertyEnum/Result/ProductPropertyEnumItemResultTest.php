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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductPropertyEnum\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result\ProductPropertyEnumItemResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Service\ProductPropertyEnum;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductPropertyEnumItemResult::class)]
class ProductPropertyEnumItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ProductPropertyEnum $productPropertyEnumService;

    private int $propertyId;

    private int $productPropertyEnumId;

    #[Test]
    #[TestDox('all fields in ProductPropertyEnumItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->productPropertyEnumService->get($this->productPropertyEnumId)
            ->getCoreResponse()->getResponseData()->getResult()['productPropertyEnum'];

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            ProductPropertyEnumItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in ProductPropertyEnumItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $productPropertyEnumItemResult = $this->productPropertyEnumService->get($this->productPropertyEnumId)->productPropertyEnum();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $productPropertyEnumItemResult,
            ProductPropertyEnumItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->productPropertyEnumService = Fabric::getServiceBuilder()->getCatalogScope()->productPropertyEnum();

        $catalogService = Fabric::getServiceBuilder()->getCatalogScope()->catalog();
        $iblockId = $catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $propertyResponse = Fabric::getCore()->call('catalog.productProperty.add', [
            'fields' => [
                'iblockId' => $iblockId,
                'name' => sprintf('test list property %s', time()),
                'propertyType' => 'L',
                'listType' => 'L',
            ],
        ]);
        $this->propertyId = (int)$propertyResponse->getResponseData()->getResult()['productProperty']['id'];

        $addResult = $this->productPropertyEnumService->add([
            'propertyId' => $this->propertyId,
            'value' => sprintf('test value %s', time()),
            'xmlId' => sprintf('test-xml-id-%s', time()),
            'def' => 'Y',
            'sort' => 100,
        ]);
        $this->productPropertyEnumId = $addResult->productPropertyEnum()->id;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        $this->productPropertyEnumService->delete($this->productPropertyEnumId);
        Fabric::getCore()->call('catalog.productProperty.delete', ['id' => $this->propertyId]);
    }
}
