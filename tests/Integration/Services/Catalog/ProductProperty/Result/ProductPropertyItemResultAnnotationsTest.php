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

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Fields\FieldsFilter;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertyItemResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Service\ProductProperty;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductPropertyItemResult::class)]
class ProductPropertyItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ProductProperty $productPropertyService;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->productPropertyService = Fabric::getServiceBuilder()->getCatalogScope()->productProperty();
    }

    #[Test]
    #[TestDox('all fields in ProductPropertyItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new FieldsFilter())->filterSystemFields(
            array_keys($this->productPropertyService->getFields()->getFieldsDescription())
        );

        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, ProductPropertyItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in ProductPropertyItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->productPropertyService->getFields()->getFieldsDescription();
        $systemFieldsCodes = (new FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            ProductPropertyItemResult::class
        );
    }
}
