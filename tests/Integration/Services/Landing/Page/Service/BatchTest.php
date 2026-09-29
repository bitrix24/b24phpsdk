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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\Page\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\Page\Result\PageItemResult;
use Bitrix24\SDK\Services\Landing\Page\Service\Batch;
use Bitrix24\SDK\Services\Landing\Page\Service\Page;
use Bitrix24\SDK\Services\Landing\Site\Service\Site;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Page $pageService;

    private Site $siteService;

    private int $siteId;

    /** @var int[] */
    private array $createdPageIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $landingServiceBuilder = Fabric::getServiceBuilder()->getLandingScope();
        $this->pageService = $landingServiceBuilder->page();
        $this->siteService = $landingServiceBuilder->site();

        $suffix = uniqid();
        $this->siteId = $this->siteService->add([
            'TITLE' => 'SDK Batch Page Site ' . $suffix,
            'CODE' => 'sdkbatchpagesite' . $suffix,
            'TYPE' => 'PAGE',
        ])->getId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->createdPageIds !== []) {
            try {
                foreach ($this->pageService->batch->delete($this->createdPageIds) as $deletedItemBatchResult) {
                    // iterate to trigger execution
                }
            } catch (\Exception) {
                // Ignore if pages don't exist
            }
        }

        try {
            $this->siteService->delete($this->siteId);
        } catch (\Exception) {
            // Ignore if site doesn't exist
        }
    }

    /**
     * @return int[]
     * @throws BaseException
     */
    private function addPages(int $count, string $titlePrefix): array
    {
        $suffix = uniqid();
        $pages = [];
        for ($i = 1; $i <= $count; $i++) {
            $pages[] = [
                'TITLE' => sprintf('%s %d %s', $titlePrefix, $i, $suffix),
                'CODE' => sprintf('sdkbatchpage%d%s', $i, $suffix),
                'SITE_ID' => $this->siteId,
            ];
        }

        $ids = [];
        foreach ($this->pageService->batch->add($pages) as $addedItemBatchResult) {
            $ids[] = $addedItemBatchResult->getId();
        }

        $this->createdPageIds = array_merge($this->createdPageIds, $ids);

        return $ids;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch add creates multiple pages')]
    public function testBatchAdd(): void
    {
        $ids = $this->addPages(3, 'SDK Batch Add Page');

        self::assertCount(3, $ids);
        foreach ($ids as $id) {
            self::assertGreaterThan(0, $id);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch list returns all pages when their count exceeds one page of results')]
    public function testBatchListSeveralPages(): void
    {
        $ids = $this->addPages(55, 'SDK Batch List Page');

        $foundIds = [];
        foreach ($this->pageService->batch->list(['ID', 'TITLE', 'SITE_ID'], ['SITE_ID' => $this->siteId]) as $pageItemResult) {
            self::assertInstanceOf(PageItemResult::class, $pageItemResult);
            $foundIds[] = (int)$pageItemResult->ID;
        }

        sort($foundIds);
        self::assertSame($ids, $foundIds);

        $limitedCount = 0;
        foreach ($this->pageService->batch->list(['ID'], ['SITE_ID' => $this->siteId], ['ID' => 'DESC'], 52) as $pageItemResult) {
            $limitedCount++;
        }

        self::assertSame(52, $limitedCount);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch update modifies multiple pages')]
    public function testBatchUpdate(): void
    {
        $ids = $this->addPages(3, 'SDK Batch Update Page');

        $updates = [];
        foreach ($ids as $id) {
            $updates[$id] = ['TITLE' => 'SDK Batch Updated Page ' . $id];
        }

        $cnt = 0;
        foreach ($this->pageService->batch->update($updates) as $updatedItemBatchResult) {
            $cnt++;
            self::assertTrue($updatedItemBatchResult->isSuccess());
        }

        self::assertSame(count($updates), $cnt);

        foreach ($this->pageService->getList(['ID', 'TITLE'], ['ID' => $ids])->getPages() as $pageItemResult) {
            self::assertSame('SDK Batch Updated Page ' . $pageItemResult->ID, $pageItemResult->TITLE);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch delete removes multiple pages')]
    public function testBatchDelete(): void
    {
        $ids = $this->addPages(3, 'SDK Batch Delete Page');

        $cnt = 0;
        foreach ($this->pageService->batch->delete($ids) as $deletedItemBatchResult) {
            $cnt++;
            self::assertTrue($deletedItemBatchResult->isSuccess());
        }

        self::assertSame(count($ids), $cnt);
        self::assertSame([], $this->pageService->getList(['ID'], ['ID' => $ids])->getPages());
        $this->createdPageIds = [];
    }
}
