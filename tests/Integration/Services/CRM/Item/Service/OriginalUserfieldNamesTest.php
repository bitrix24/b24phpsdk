<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Item\Service;

use Bitrix24\SDK\Services\CRM\Item\Service\Item;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Item::class)]
class OriginalUserfieldNamesTest extends TestCase
{
    public function testOriginalNamesAcrossMetadataFilteringAndMultipleBatchPages(): void
    {
        $crm = Factory::getServiceBuilder()->getCRMScope();
        $type = $crm->type()->add('SDK original UF read test ' . uniqid(), null, ['isCategoriesEnabled' => 'Y'])->type();
        try {
            $entityTypeId = $type->entityTypeId;
            $field = $crm->userfieldConfig()->add('crm', [
                'entityId' => 'CRM_' . $type->id,
                'fieldName' => 'UF_CRM_' . $type->id . '_SDK_READ',
                'userTypeId' => 'string',
            ])->field()->fieldName;
            $fields = $crm->item()->fields($entityTypeId, true)->getFieldsDescription()['fields'];
            self::assertArrayHasKey($field, $fields);
            $camelName = null;
            foreach ($crm->item()->fields($entityTypeId)->getFieldsDescription()['fields'] as $name => $description) {
                if (($description['upperName'] ?? null) === $field) {
                    $camelName = $name;
                }
            }

            self::assertNotNull($camelName);
            $items = [];
            for ($i = 0; $i < 55; $i++) {
                $items[] = ['title' => 'SDK item ' . $i, $camelName => 'SDK value ' . $i];
            }

            $added = iterator_to_array($crm->item()->batch->add($entityTypeId, $items));
            self::assertCount(55, $added);
            $first = $crm->item()->get($entityTypeId, $added[0]->id, true)->item();
            self::assertSame('SDK value 0', $first->$field);
            $filter = ['=' . $field => 'SDK value 0'];
            self::assertSame(1, $crm->item()->countByFilter($entityTypeId, $filter, true));
            self::assertCount(1, $crm->item()->list($entityTypeId, [], $filter, ['id', $field], 0, true)->getItems());
            $all = iterator_to_array($crm->item()->batch->list($entityTypeId, ['id' => 'ASC'], [], ['*'], null, true));
            self::assertCount(55, $all);
            self::assertCount(55, array_unique(array_map(static fn ($item): int => $item->id, $all)));
            foreach (array_values($all) as $index => $item) {
                self::assertSame('SDK value ' . $index, $item->$field);
            }

            $limited = iterator_to_array($crm->item()->batch->list($entityTypeId, ['id' => 'ASC'], [], ['*'], 3, true));
            self::assertCount(3, $limited);
            $categories = $crm->category()->list($entityTypeId)->getCategories();
            self::assertNotEmpty($categories);
            self::assertSame($entityTypeId, $categories[0]->entityTypeId);
        } finally {
            foreach ($crm->item()->batch->list($type->entityTypeId, ['id' => 'ASC'], [], ['id']) as $item) {
                $deleted = $crm->item()->delete($type->entityTypeId, $item->id);
                $deleted->getCoreResponse()->getResponseData();
                self::assertTrue($deleted->isSuccess());
            }

            self::assertTrue($crm->type()->delete($type->id)->isSuccess());
            self::assertSame([], $crm->type()->list(filter: ['id' => $type->id])->getTypes());
        }
    }
}
