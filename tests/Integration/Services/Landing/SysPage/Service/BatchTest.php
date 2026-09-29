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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\SysPage\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\Page\Service\Page;
use Bitrix24\SDK\Services\Landing\Site\Service\Site;
use Bitrix24\SDK\Services\Landing\SysPage\Service\Batch;
use Bitrix24\SDK\Services\Landing\SysPage\Service\SysPage;
use Bitrix24\SDK\Services\Landing\SysPage\SysPageType;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private SysPage $sysPageService;

    private Page $pageService;

    private Site $siteService;

    private int $siteId;

    /** @var int[] */
    private array $createdPageIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $landingServiceBuilder = Factory::getServiceBuilder(true)->getLandingScope();
        $this->sysPageService = $landingServiceBuilder->sysPage();
        $this->pageService = $landingServiceBuilder->page();
        $this->siteService = $landingServiceBuilder->site();

        $suffix = uniqid();
        $this->siteId = $this->siteService->add([
            'TITLE' => 'SDK Batch SysPage Site ' . $suffix,
            'CODE' => 'sdkbatchsyspagesite' . $suffix,
            'TYPE' => 'PAGE',
        ])->getId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        try {
            $this->sysPageService->deleteForSite($this->siteId);
        } catch (\Exception) {
            // Ignore cleanup errors
        }

        foreach ($this->createdPageIds as $createdPageId) {
            try {
                $this->pageService->delete($createdPageId);
            } catch (\Exception) {
                // Ignore if page doesn't exist
            }
        }

        try {
            $this->siteService->delete($this->siteId);
        } catch (\Exception) {
            // Ignore if site doesn't exist
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch set assigns and removes special pages')]
    public function testBatchSet(): void
    {
        $suffix = uniqid();
        $pages = [];
        for ($i = 1; $i <= 2; $i++) {
            $pages[] = [
                'TITLE' => sprintf('SDK Batch SysPage Page %d %s', $i, $suffix),
                'CODE' => sprintf('sdkbatchsyspagepage%d%s', $i, $suffix),
                'SITE_ID' => $this->siteId,
            ];
        }

        foreach ($this->pageService->batch->add($pages) as $addedItemBatchResult) {
            $this->createdPageIds[] = $addedItemBatchResult->getId();
        }

        [$catalogPageId, $cartPageId] = $this->createdPageIds;

        $cnt = 0;
        foreach ($this->sysPageService->batch->set([
            ['siteId' => $this->siteId, 'type' => SysPageType::catalog, 'pageId' => $catalogPageId],
            ['siteId' => $this->siteId, 'type' => 'cart', 'pageId' => $cartPageId],
        ]) as $updatedItemBatchResult) {
            $cnt++;
            self::assertTrue($updatedItemBatchResult->isSuccess());
        }

        self::assertSame(2, $cnt);

        $sysPages = $this->sysPageService->get($this->siteId)->getCoreResponse()->getResponseData()->getResult();
        self::assertSame($catalogPageId, (int)$sysPages['catalog']['LANDING_ID']);
        self::assertSame($cartPageId, (int)$sysPages['cart']['LANDING_ID']);

        // remove special page binding without page identifier
        foreach ($this->sysPageService->batch->set([
            ['siteId' => $this->siteId, 'type' => SysPageType::cart],
        ]) as $updatedItemBatchResult) {
            self::assertTrue($updatedItemBatchResult->isSuccess());
        }

        $sysPages = $this->sysPageService->get($this->siteId)->getCoreResponse()->getResponseData()->getResult();
        self::assertArrayHasKey('catalog', $sysPages);
        self::assertArrayNotHasKey('cart', $sysPages);
    }

    /**
     * @throws BaseException
     */
    #[TestDox('batch set throws exception when required keys are missing')]
    public function testBatchSetWithoutRequiredKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @phpstan-ignore-next-line intentionally invalid input */
        foreach ($this->sysPageService->batch->set([['type' => 'cart']]) as $updatedItemBatchResult) {
            // iterate to trigger execution
        }
    }
}
