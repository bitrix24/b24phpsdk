<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\CallList\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\CRM\CallList\Service\Batch;
use Bitrix24\SDK\Tests\Integration\Services\CRM\CallList\CallListTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Batch::class)]
class BatchTest extends CallListTestCase
{
    public function testBatchAddAndUpdate(): void
    {
        $inputs = array_map(static fn (int $id): array => ['ENTITY_TYPE' => 'CONTACT', 'ENTITIES' => [$id]], self::$contactIds);
        $ids = [];
        // Collect IDs as they arrive so successful creations are recorded even if a later command fails.
        foreach ($this->callList->batch->add($inputs) as $result) {
            $ids[] = $result->getId();
            self::$retainedListIds[] = $result->getId();
        }

        self::assertCount(2, $ids);
        foreach ($ids as $index => $id) {
            self::assertSame(self::$contactIds[$index], $this->callList->getItems($id)->getItems()[0]->ID);
        }

        $updates = [];
        foreach ($ids as $id) {
            $updates[$id] = ['ENTITY_TYPE' => 'CONTACT', 'ENTITIES' => self::$contactIds];
        }

        $results = iterator_to_array($this->callList->batch->update($updates));
        self::assertCount(2, $results);
        foreach ($results as $result) {
            self::assertTrue($result->isSuccess());
        }

        foreach ($ids as $id) {
            self::assertCount(2, $this->callList->getItems($id)->getItems());
        }
    }

    public function testBatchZeroWebformIdPropagatesApiError(): void
    {
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('webform_error');
        iterator_to_array($this->callList->batch->update([
            self::$listId => ['ENTITY_TYPE' => 'CONTACT', 'ENTITIES' => self::$contactIds, 'WEBFORM_ID' => 0],
        ]));
    }

    public function testTraversableListMatchesNativeOrderFilterAndLimit(): void
    {
        foreach ([['ID' => 'ASC'], ['ID' => 'DESC'], ['DATE_CREATE' => 'DESC']] as $order) {
            $filter = ['ID' => self::$listId];
            $native = $this->callList->list($order, $filter, ['ID'])->getCallLists();
            $batch = iterator_to_array($this->callList->batch->list($order, $filter, ['ID']));
            self::assertSame(array_map(static fn ($item): int => $item->ID, $native), array_map(static fn ($item): int => $item->ID, $batch));
            self::assertCount(1, iterator_to_array($this->callList->batch->list($order, $filter, ['ID'], 1)));
        }

        self::assertSame([], iterator_to_array($this->callList->batch->list([], [], [], 0)));
        self::assertSame([], iterator_to_array($this->callList->batch->add([])));
        self::assertSame([], iterator_to_array($this->callList->batch->update([])));
    }
}
