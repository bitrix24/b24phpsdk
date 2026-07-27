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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductPropertyFeature\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureItemResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Service\ProductPropertyFeature;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductPropertyFeatureItemResult::class)]
class ProductPropertyFeatureItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ProductPropertyFeature $productPropertyFeatureService;

    private int $propertyId;

    #[\Override]
    protected function setUp(): void
    {
        $this->productPropertyFeatureService = Fabric::getServiceBuilder()->getCatalogScope()->productPropertyFeature();
        $this->propertyId = $this->createProductProperty();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->deleteProductProperty($this->propertyId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in ProductPropertyFeatureItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawFields = $this->productPropertyFeatureService->getFields()->getFieldsDescription();

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawFields), ProductPropertyFeatureItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in ProductPropertyFeatureItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $item = $this->productPropertyFeatureService->add([
            'propertyId' => $this->propertyId,
            'moduleId' => 'iblock',
            'featureId' => 'LIST_PAGE_SHOW',
            'isEnabled' => 'Y',
        ])->productPropertyFeature();

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($item, ProductPropertyFeatureItemResult::class);
    }

    protected function createProductProperty(): int
    {
        $core = Fabric::getCore();
        $iblockId = (int)$core->call('catalog.catalog.list', [
            'select' => ['id', 'iblockId'],
        ])->getResponseData()->getResult()['catalogs'][0]['iblockId'];

        return (int)$core->call('catalog.productProperty.add', [
            'fields' => [
                'iblockId' => $iblockId,
                'name' => 'SDK Test Property ' . uniqid('', true),
                'propertyType' => 'S',
                'active' => 'Y',
                'sort' => 100,
            ],
        ])->getResponseData()->getResult()['productProperty']['id'];
    }

    protected function deleteProductProperty(int $id): void
    {
        $core = Fabric::getCore();
        $core->call('catalog.productProperty.delete', [
            'id' => $id,
        ]);
    }
}
