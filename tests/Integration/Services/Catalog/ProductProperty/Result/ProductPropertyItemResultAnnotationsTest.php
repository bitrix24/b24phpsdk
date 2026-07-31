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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductProperty\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertyItemResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Service\ProductProperty;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductPropertyItemResult::class)]
class ProductPropertyItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ProductProperty $productPropertyService;

    private Catalog $catalogService;

    private Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->productPropertyService = Factory::getServiceBuilder()->getCatalogScope()->productProperty();
        $this->catalogService = Factory::getServiceBuilder()->getCatalogScope()->catalog();
        $this->faker = FakerFactory::create();
    }

    /**
     * Helper: create a product property, fetch it via get() to obtain the full field set, then delete it.
     *
     * @return array<string, mixed>
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstProductPropertyRawItem(): array
    {
        $iblockId = $this->catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $id = $this->productPropertyService->add([
            'iblockId' => $iblockId,
            'name' => 'SDK_ANNOT_TEST_' . $this->faker->uuid(),
            'propertyType' => 'S',
        ])->productProperty()->id;

        $rawItem = $this->productPropertyService->get($id)
            ->getCoreResponse()->getResponseData()->getResult()['productProperty'] ?? [];

        try {
            $this->productPropertyService->delete($id);
        } catch (BaseException) {
            // Server-side error during cleanup; must not affect annotations test
        }

        self::assertNotEmpty($rawItem, 'get() must return a product property item to run this test');

        return $rawItem;
    }

    #[Test]
    #[TestDox('all fields in ProductPropertyItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->getFirstProductPropertyRawItem();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            ProductPropertyItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in ProductPropertyItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $rawItem = $this->getFirstProductPropertyRawItem();
        $productPropertyItemResult = new ProductPropertyItemResult($rawItem);

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $productPropertyItemResult,
            ProductPropertyItemResult::class
        );
    }
}
