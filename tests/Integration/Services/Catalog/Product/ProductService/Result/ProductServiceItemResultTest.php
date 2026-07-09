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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Product\ProductService\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Result\ProductServiceItemResult;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Service\ProductService;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductServiceItemResult::class)]
class ProductServiceItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ProductService $productServiceScope;

    private int $serviceId;

    private int $iblockId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->productServiceScope = $serviceBuilder->getCatalogScope()->productService();
        $catalogService = $serviceBuilder->getCatalogScope()->catalog();
        $this->iblockId = $catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $this->serviceId = $this->productServiceScope->add([
            'iblockId' => $this->iblockId,
            'name' => sprintf('test service annotations %s', time()),
        ])->productService()->id;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        $this->productServiceScope->delete($this->serviceId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in ProductServiceItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->productServiceScope->get($this->serviceId)->getCoreResponse()
            ->getResponseData()->getResult()['service'];

        // dynamic catalog properties (propertyN) vary per portal and are intentionally not annotated
        $fieldCodes = array_filter(
            array_keys($rawItem),
            static fn (string $fieldCode): bool => in_array(preg_match('/^property\d+$/', $fieldCode), [0, false], true)
        );

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $fieldCodes,
            ProductServiceItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in ProductServiceItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        // priceType is present only in fieldsByFilter response, not in item response, and is not annotated
        // dynamic catalog properties (propertyN) vary per portal and are intentionally not annotated
        $fields = array_filter(
            $this->productServiceScope->fieldsByFilter($this->iblockId)->getFieldsDescription()['service'],
            static fn (string $fieldCode): bool => $fieldCode !== 'priceType' && in_array(preg_match('/^property\d+$/', $fieldCode), [0, false], true),
            ARRAY_FILTER_USE_KEY
        );
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $fields,
            ProductServiceItemResult::class
        );
    }
}
