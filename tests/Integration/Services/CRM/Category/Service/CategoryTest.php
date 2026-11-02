<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Category\Service;

use Bitrix24\SDK\Services\CRM\Category\Service\Category;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Category::class)]
class CategoryTest extends TestCase
{
    public function testReadBuiltInCategoriesAndMetadata(): void
    {
        $category = Factory::getServiceBuilder()->getCRMScope()->category();
        $items = $category->list(2)->getCategories();
        self::assertNotEmpty($items);
        $item = $items[0];
        $loaded = $category->get(2, $item->id)->category();
        self::assertSame($item->name, $loaded->name);
        self::assertSame(2, $loaded->entityTypeId);
        self::assertArrayHasKey('id', $category->fields(2)->getFieldsDescription()['fields']);
    }
}
