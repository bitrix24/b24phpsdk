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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\Block\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\Block\Result\BlockItemResult;
use Bitrix24\SDK\Services\Landing\Block\Service\Batch;
use Bitrix24\SDK\Services\Landing\Block\Service\Block;
use Bitrix24\SDK\Services\Landing\Page\Service\Page;
use Bitrix24\SDK\Services\Landing\Site\Service\Site;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Block $blockService;

    private Page $pageService;

    private Site $siteService;

    private int $siteId;

    /** @var int[] */
    private array $createdPageIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $landingServiceBuilder = Fabric::getServiceBuilder()->getLandingScope();
        $this->blockService = $landingServiceBuilder->block();
        $this->pageService = $landingServiceBuilder->page();
        $this->siteService = $landingServiceBuilder->site();

        $suffix = uniqid();
        $this->siteId = $this->siteService->add([
            'TITLE' => 'SDK Batch Block Site ' . $suffix,
            'CODE' => 'sdkbatchblocksite' . $suffix,
            'TYPE' => 'PAGE',
        ])->getId();
    }

    #[\Override]
    protected function tearDown(): void
    {
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
    #[TestDox('batch list returns blocks of several pages')]
    public function testBatchList(): void
    {
        $suffix = uniqid();
        $expectedBlocks = [];
        for ($i = 1; $i <= 2; $i++) {
            $pageId = $this->pageService->add([
                'TITLE' => sprintf('SDK Batch Block Page %d %s', $i, $suffix),
                'CODE' => sprintf('sdkbatchblockpage%d%s', $i, $suffix),
                'SITE_ID' => $this->siteId,
            ])->getId();
            $this->createdPageIds[] = $pageId;

            $blockId = $this->pageService->addBlock($pageId, [
                'CODE' => '01.big_with_text',
                'ACTIVE' => 'Y',
            ])->getId();
            $expectedBlocks[$blockId] = $pageId;
        }

        $foundBlocks = [];
        foreach ($this->blockService->batch->list($this->createdPageIds, ['edit_mode' => 1]) as $blockItemResult) {
            self::assertInstanceOf(BlockItemResult::class, $blockItemResult);
            $foundBlocks[(int)$blockItemResult->id] = (int)$blockItemResult->lid;
        }

        foreach ($expectedBlocks as $blockId => $pageId) {
            self::assertArrayHasKey($blockId, $foundBlocks);
            self::assertSame($pageId, $foundBlocks[$blockId]);
        }
    }

    /**
     * @throws BaseException
     */
    #[TestDox('batch list throws exception on invalid page id type')]
    public function testBatchListWithInvalidPageId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @phpstan-ignore-next-line intentionally invalid input */
        foreach ($this->blockService->batch->list(['1']) as $blockItemResult) {
            // iterate to trigger execution
        }
    }
}
