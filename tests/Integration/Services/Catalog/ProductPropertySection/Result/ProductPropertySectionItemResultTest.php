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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductPropertySection\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result\ProductPropertySectionItemResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Service\ProductPropertySection;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductPropertySectionItemResult::class)]
class ProductPropertySectionItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ProductPropertySection $productPropertySection;

    private int $iblockId;

    private int $propertyId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in ProductPropertySectionItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->productPropertySection
            ->set($this->propertyId, [
                'smartFilter' => 'Y',
                'displayType' => 'F',
                'displayExpanded' => 'N',
                'filterHint' => 'test hint',
            ])
            ->getCoreResponse()->getResponseData()->getResult()['productPropertySection'];

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            ProductPropertySectionItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->productPropertySection = Fabric::getServiceBuilder()
            ->getCatalogScope()
            ->productPropertySection();

        $this->iblockId = Fabric::getServiceBuilder()->getCatalogScope()->catalog()
            ->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $response = Fabric::getCore()->call('catalog.productProperty.add', [
            'fields' => [
                'iblockId' => $this->iblockId,
                'name' => sprintf('test property %s', time()),
                'propertyType' => 'S',
                'code' => sprintf('TEST_PROP_%s', time()),
                'active' => 'Y',
            ],
        ]);
        $this->propertyId = (int)$response->getResponseData()->getResult()['productProperty']['id'];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        Fabric::getCore()->call('catalog.productProperty.delete', ['id' => $this->propertyId]);
    }
}
