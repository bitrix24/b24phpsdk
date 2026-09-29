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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Userfieldconfig\Service;

use Bitrix24\SDK\Services\CRM\Type\Service\Type;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Service\Batch;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Service\Userfieldconfig;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    public const FIELD_LETTERS = ['A', 'B', 'C'];

    private Userfieldconfig $userfieldConfigService;

    private Type $typeService;

    private int $scratchTypeId;

    private string $entityId;

    #[\Override]
    protected function setUp(): void
    {
        $this->userfieldConfigService = Factory::getServiceBuilder()->getCRMScope()->userfieldConfig();
        $this->typeService = Factory::getServiceBuilder()->getCRMScope()->type();

        // entityId for userfieldconfig is CRM_{id} built from the SPA type's own `id`,
        // not from `entityTypeId` (see docs for onCrmTypeUserFieldAdd, section "ENTITY_ID")
        $addedType = $this->typeService->add(sprintf('%s userfieldconfig batch test SPA type', time()));
        $this->scratchTypeId = $addedType->getId();
        $this->entityId = sprintf('CRM_%d', $this->scratchTypeId);
    }

    #[\Override]
    protected function tearDown(): void
    {
        // deleting the SPA type also removes its custom fields
        $this->typeService->delete($this->scratchTypeId);
    }

    public function testBatchAdd(): void
    {
        $fieldNames = $this->buildFieldNames();
        $items = [];
        foreach ($fieldNames as $fieldName) {
            $items[] = [
                'entityId' => $this->entityId,
                'fieldName' => $fieldName,
                'userTypeId' => 'string',
            ];
        }

        $cnt = 0;
        $addedFieldNames = [];
        foreach ($this->userfieldConfigService->batch->add('crm', $items) as $result) {
            $addedFieldNames[] = $result->field()->fieldName;
            $cnt++;
        }

        $this->assertEquals(count($items), $cnt);
        $this->assertEqualsCanonicalizing($fieldNames, $addedFieldNames);
    }

    public function testBatchUpdate(): void
    {
        $fieldNames = $this->buildFieldNames();
        $itemsToAdd = [];
        foreach ($fieldNames as $fieldName) {
            $itemsToAdd[] = [
                'entityId' => $this->entityId,
                'fieldName' => $fieldName,
                'userTypeId' => 'string',
            ];
        }

        $ids = [];
        foreach ($this->userfieldConfigService->batch->add('crm', $itemsToAdd) as $result) {
            $ids[] = $result->getId();
        }

        $itemsToUpdate = [];
        foreach ($ids as $id) {
            $itemsToUpdate[$id] = ['mandatory' => 'Y'];
        }

        $cnt = 0;
        foreach ($this->userfieldConfigService->batch->update('crm', $itemsToUpdate) as $updateResult) {
            $this->assertTrue($updateResult->field()->mandatory);
            $cnt++;
        }

        $this->assertEquals(count($ids), $cnt);
    }

    public function testBatchDelete(): void
    {
        $fieldNames = $this->buildFieldNames();
        $itemsToAdd = [];
        foreach ($fieldNames as $fieldName) {
            $itemsToAdd[] = [
                'entityId' => $this->entityId,
                'fieldName' => $fieldName,
                'userTypeId' => 'string',
            ];
        }

        $ids = [];
        foreach ($this->userfieldConfigService->batch->add('crm', $itemsToAdd) as $result) {
            $ids[] = $result->getId();
        }

        $cnt = 0;
        foreach ($this->userfieldConfigService->batch->delete('crm', $ids) as $deleteResult) {
            $this->assertTrue($deleteResult->isSuccess());
            $cnt++;
        }

        $this->assertEquals(count($ids), $cnt);
    }

    /**
     * @return non-empty-string[]
     */
    private function buildFieldNames(): array
    {
        $fieldNames = [];
        foreach (self::FIELD_LETTERS as $letter) {
            $fieldNames[] = sprintf('UF_%s_%d%s', $this->entityId, time(), $letter);
        }

        return $fieldNames;
    }
}
