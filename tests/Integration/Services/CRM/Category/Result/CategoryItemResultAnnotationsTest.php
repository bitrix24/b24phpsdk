<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Category\Result;

use Bitrix24\SDK\Services\CRM\Category\Result\CategoryItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Typhoon\Reflection\TyphoonReflector;

#[CoversClass(CategoryItemResult::class)]
class CategoryItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    public function testAllSystemFieldsAnnotated(): void
    {
        $category = Factory::getServiceBuilder()->getCRMScope()->category();
        $fields = $category->fields(2)->getFieldsDescription()['fields'];
        // Metadata is portal-dependent; origin fields are optional. Require every
        // live system field to be annotated, without requiring optional fields to exist.
        $annotations = TyphoonReflector::build()->reflectClass(CategoryItemResult::class)->properties();
        foreach (array_keys($fields) as $field) {
            self::assertTrue(isset($annotations[$field]) && $annotations[$field]->isAnnotated());
        }

        foreach ($category->list(2)->getCategories() as $item) {
            $raw = $category->get(2, $item->id)->getCoreResponse()->getResponseData()->getResult()['category'];
            foreach (array_keys($raw) as $field) {
                self::assertTrue(isset($annotations[$field]) && $annotations[$field]->isAnnotated());
            }
        }
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $category = Factory::getServiceBuilder()->getCRMScope()->category();
        $fields = $category->fields(2)->getFieldsDescription()['fields'];
        // The shared assertion uses the legacy name "bool" for the universal
        // metadata type "boolean"; both describe the same bool result contract.
        foreach ($fields as &$field) {
            if ($field['type'] === 'boolean') {
                $field['type'] = 'bool';
            }
        }

        unset($field);
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, CategoryItemResult::class);
        foreach ($category->list(2)->getCategories() as $item) {
            $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($category->get(2, $item->id)->category(), CategoryItemResult::class);
        }
    }
}
