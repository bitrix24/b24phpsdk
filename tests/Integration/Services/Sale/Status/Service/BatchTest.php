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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\Status\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\Status\Service\Batch;
use Bitrix24\SDK\Services\Sale\Status\Service\Status;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\Status\Batch::class)]
class BatchTest extends TestCase
{
    private const ITEMS_COUNT = 3;

    private Status $statusService;

    /**
     * @var string[]
     */
    private array $statusIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->statusService = Fabric::getServiceBuilder()->getSaleScope()->status();

        // status identifier is limited to 2 characters, choose free identifiers
        $existingIds = [];
        foreach ($this->statusService->list(['id'])->getStatuses() as $statusItemResult) {
            $existingIds[] = (string)$statusItemResult->id;
        }

        // sale.status.delete does not delete status localizations, so skip identifiers with localizations left
        $statusLangService = Fabric::getServiceBuilder()->getSaleScope()->statusLang();
        foreach (range('A', 'Z') as $letter) {
            $candidateId = 'Q' . $letter;
            if (!in_array($candidateId, $existingIds, true)
                && $statusLangService->list([], ['statusId' => $candidateId])->getStatusLangs() === []) {
                $this->statusIds[] = $candidateId;
            }

            if (count($this->statusIds) === self::ITEMS_COUNT) {
                break;
            }
        }

        if (count($this->statusIds) < self::ITEMS_COUNT) {
            $this->markTestSkipped('No free status identifiers available for testing');
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        $statusLangService = Fabric::getServiceBuilder()->getSaleScope()->statusLang();
        $ids = $this->statusIds;
        foreach ($ids as $id) {
            try {
                $statusLangService->deleteByFilter(['statusId' => $id, 'lid' => 'en', 'name' => 'Batch status ' . $id]);
            } catch (\Throwable) {
                // localization was not created
            }

            try {
                $this->statusService->delete($id);
            } catch (\Throwable) {
                // status is already deleted by the test
            }
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::list, Batch::delete statuses')]
    public function testAddUpdateListDelete(): void
    {
        $items = [];
        foreach ($this->statusIds as $statusId) {
            $items[] = [
                'id' => $statusId,
                'type' => 'O',
                'sort' => 900,
                'notify' => 'N',
                'name' => ['en' => 'Batch status ' . $statusId],
            ];
        }

        $addedIds = [];
        foreach ($this->statusService->batch->add($items) as $addedItemResult) {
            $addedIds[] = $addedItemResult->getId();
        }

        $this->assertSame($this->statusIds, $addedIds);

        $updates = [];
        foreach ($addedIds as $statusId) {
            $updates[$statusId] = ['type' => 'O', 'sort' => 950];
        }

        foreach ($this->statusService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        $listed = [];
        foreach ($this->statusService->batch->list(['id', 'sort'], ['type' => 'O']) as $item) {
            $listed[(string)$item->id] = (int)$item->sort;
        }

        foreach ($addedIds as $addedId) {
            $this->assertArrayHasKey($addedId, $listed);
            $this->assertSame(950, $listed[$addedId]);
        }

        $deletedCount = 0;
        foreach ($this->statusService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
