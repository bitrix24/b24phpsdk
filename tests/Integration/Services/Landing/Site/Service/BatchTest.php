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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\Site\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\Site\Result\SiteItemResult;
use Bitrix24\SDK\Services\Landing\Site\Service\Batch;
use Bitrix24\SDK\Services\Landing\Site\Service\Site;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Site $siteService;

    /** @var int[] */
    private array $createdSiteIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->siteService = Factory::getServiceBuilder()->getLandingScope()->site();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdSiteIds as $createdSiteId) {
            try {
                $this->siteService->delete($createdSiteId);
            } catch (\Exception) {
                // Ignore if site doesn't exist
            }
        }
    }

    /**
     * @return int[]
     * @throws BaseException
     */
    private function addSites(int $count, string $titlePrefix): array
    {
        $suffix = uniqid();
        $sites = [];
        for ($i = 1; $i <= $count; $i++) {
            $sites[] = [
                'TITLE' => sprintf('%s %d %s', $titlePrefix, $i, $suffix),
                'CODE' => sprintf('sdkbatchsite%d%s', $i, $suffix),
                'TYPE' => 'PAGE',
            ];
        }

        $ids = [];
        foreach ($this->siteService->batch->add($sites) as $addedItemBatchResult) {
            $ids[] = $addedItemBatchResult->getId();
        }

        $this->createdSiteIds = array_merge($this->createdSiteIds, $ids);

        return $ids;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch add creates multiple sites')]
    public function testBatchAdd(): void
    {
        $ids = $this->addSites(3, 'SDK Batch Add Site');

        self::assertCount(3, $ids);
        foreach ($ids as $id) {
            self::assertGreaterThan(0, $id);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch list returns sites with filter and limit')]
    public function testBatchList(): void
    {
        $ids = $this->addSites(3, 'SDK Batch List Site');

        $foundIds = [];
        foreach ($this->siteService->batch->list(['ID', 'TITLE'], ['ID' => $ids]) as $siteItemResult) {
            self::assertInstanceOf(SiteItemResult::class, $siteItemResult);
            $foundIds[] = (int)$siteItemResult->ID;
        }

        sort($foundIds);
        self::assertSame($ids, $foundIds);

        $limitedCount = 0;
        foreach ($this->siteService->batch->list(['ID'], ['ID' => $ids], [], 2) as $siteItemResult) {
            $limitedCount++;
        }

        self::assertSame(2, $limitedCount);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch update modifies multiple sites')]
    public function testBatchUpdate(): void
    {
        $ids = $this->addSites(3, 'SDK Batch Update Site');

        $updates = [];
        foreach ($ids as $id) {
            $updates[$id] = ['TITLE' => 'SDK Batch Updated Site ' . $id];
        }

        $cnt = 0;
        foreach ($this->siteService->batch->update($updates) as $updatedItemBatchResult) {
            $cnt++;
            self::assertTrue($updatedItemBatchResult->isSuccess());
        }

        self::assertSame(count($updates), $cnt);

        foreach ($this->siteService->getList(['ID', 'TITLE'], ['ID' => $ids])->getSites() as $siteItemResult) {
            self::assertSame('SDK Batch Updated Site ' . $siteItemResult->ID, $siteItemResult->TITLE);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch delete removes multiple sites')]
    public function testBatchDelete(): void
    {
        $ids = $this->addSites(3, 'SDK Batch Delete Site');

        $cnt = 0;
        foreach ($this->siteService->batch->delete($ids) as $deletedItemBatchResult) {
            $cnt++;
            self::assertTrue($deletedItemBatchResult->isSuccess());
        }

        self::assertSame(count($ids), $cnt);
        self::assertSame([], $this->siteService->getList(['ID'], ['ID' => $ids])->getSites());
        $this->createdSiteIds = [];
    }
}
