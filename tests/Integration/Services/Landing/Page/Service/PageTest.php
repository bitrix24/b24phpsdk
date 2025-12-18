<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\Page\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\Page\Result\PageItemResult;
use Bitrix24\SDK\Services\Landing\Page\Service\Page;
use Bitrix24\SDK\Services\Landing\Site\Service\Site;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class PageTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Landing\Page\Service
 */
#[CoversMethod(Page::class, 'add')]
#[CoversMethod(Page::class, 'addByTemplate')]
#[CoversMethod(Page::class, 'copy')]
#[CoversMethod(Page::class, 'delete')]
#[CoversMethod(Page::class, 'update')]
#[CoversMethod(Page::class, 'getList')]
#[CoversMethod(Page::class, 'getAdditionalFields')]
#[CoversMethod(Page::class, 'getPreview')]
#[CoversMethod(Page::class, 'getPublicUrl')]
#[CoversMethod(Page::class, 'resolveIdByPublicUrl')]
#[CoversMethod(Page::class, 'publish')]
#[CoversMethod(Page::class, 'unpublish')]
#[CoversMethod(Page::class, 'markDeleted')]
#[CoversMethod(Page::class, 'markUnDeleted')]
#[CoversMethod(Page::class, 'move')]
#[CoversMethod(Page::class, 'removeEntities')]
#[CoversMethod(Page::class, 'addBlock')]
#[CoversMethod(Page::class, 'copyBlock')]
#[CoversMethod(Page::class, 'deleteBlock')]
#[CoversMethod(Page::class, 'moveBlockDown')]
#[CoversMethod(Page::class, 'moveBlockUp')]
#[CoversMethod(Page::class, 'moveBlock')]
#[CoversMethod(Page::class, 'hideBlock')]
#[CoversMethod(Page::class, 'showBlock')]
#[CoversMethod(Page::class, 'markBlockDeleted')]
#[CoversMethod(Page::class, 'markBlockUnDeleted')]
#[CoversMethod(Page::class, 'addBlockToFavorites')]
#[CoversMethod(Page::class, 'removeBlockFromFavorites')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Landing\Page\Service\Page::class)]
class PageTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Page $pageService;

    protected Site $siteService;

    protected array $createdPageIds = [];

    protected array $createdSiteIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->pageService = $serviceBuilder->getLandingScope()->page();
        $this->siteService = $serviceBuilder->getLandingScope()->site();
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Clean up created pages
        foreach ($this->createdPageIds as $createdPageId) {
            try {
                $this->pageService->delete($createdPageId);
            } catch (\Exception) {
                // Ignore if page doesn't exist
            }
        }

        // Clean up created sites
        foreach ($this->createdSiteIds as $createdSiteId) {
            try {
                $this->siteService->delete($createdSiteId);
            } catch (\Exception) {
                // Ignore if site doesn't exist
            }
        }
    }

    /**
     * Helper method to create a test site
     */
    protected function createTestSite(): int
    {
        $siteFields = [
            'TITLE' => 'Test Site for Page ' . time(),
            'CODE' => 'testsitepage' . time(),
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        return $siteId;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $siteId = $this->createTestSite();

        $pageFields = [
            'TITLE' => 'Test Page ' . time(),
            'CODE' => 'testpage' . time(),
            'SITE_ID' => $siteId,
            'ADDITIONAL_FIELDS' => [
                'THEME_CODE' => 'wedding'
            ]
        ];

        $addedItemResult = $this->pageService->add($pageFields);
        $pageId = $addedItemResult->getId();
        $this->createdPageIds[] = $pageId;

        self::assertGreaterThan(0, $pageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAddByTemplate(): void
    {
        $siteId = $this->createTestSite();

        // Get available page templates from portal
        $core = Factory::getCore();
        $templatesResponse = $core->call('landing.demos.getPageList', ['type' => 'page']);
        $templates = $templatesResponse->getResponseData()->getResult();

        // Use the first available template
        $templateCode = key($templates);

        $addedItemResult = $this->pageService->addByTemplate(
            $siteId,
            $templateCode,
            [
                'TITLE' => 'Test Page by Template ' . time(),
                'DESCRIPTION' => 'Test page description'
            ]
        );

        $pageId = $addedItemResult->getId();
        $this->createdPageIds[] = $pageId;

        self::assertGreaterThan(0, $pageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetList(): void
    {
        $siteId = $this->createTestSite();

        // First create a test page
        $timestamp = time();
        $pageFields = [
            'TITLE' => 'Test Page for List ' . $timestamp,
            'CODE' => 'testpagelist' . $timestamp,
            'SITE_ID' => $siteId
        ];

        $addedItemResult = $this->pageService->add($pageFields);
        $pageId = $addedItemResult->getId();
        $this->createdPageIds[] = $pageId;

        // Test getList with no parameters
        $pagesResult = $this->pageService->getList();
        $pages = $pagesResult->getPages();

        self::assertIsArray($pages);
        self::assertNotEmpty($pages);

        // Check that our created page is in the list
        $foundPage = null;
        foreach ($pages as $page) {
            self::assertInstanceOf(PageItemResult::class, $page);
            if (intval($page->ID) === $pageId) {
                $foundPage = $page;
                break;
            }
        }

        self::assertNotNull($foundPage, 'Created page should be found in the list');
        self::assertEquals($pageFields['TITLE'], $foundPage->TITLE);
        self::assertStringContainsString($pageFields['CODE'], $foundPage->CODE);
        self::assertEquals($pageFields['SITE_ID'], $foundPage->SITE_ID);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetListWithFilters(): void
    {
        $siteId = $this->createTestSite();

        // Create a test page
        $timestamp = time();
        $pageFields = [
            'TITLE' => 'Test Page Filter ' . $timestamp,
            'CODE' => 'testpagefilter' . $timestamp,
            'SITE_ID' => $siteId
        ];

        $addedItemResult = $this->pageService->add($pageFields);
        $pageId = $addedItemResult->getId();
        $this->createdPageIds[] = $pageId;

        // Test getList with filters
        $pagesResult = $this->pageService->getList(
            ['ID', 'TITLE', 'CODE', 'SITE_ID'],
            ['SITE_ID' => $siteId],
            ['ID' => 'DESC']
        );

        $pages = $pagesResult->getPages();

        self::assertIsArray($pages);

        // All pages should belong to our site
        foreach ($pages as $page) {
            self::assertEquals($siteId, $page->SITE_ID);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $siteId = $this->createTestSite();

        // Create a test page
        $pageFields = [
            'TITLE' => 'Test Page for Update ' . time(),
            'CODE' => 'testpageupdate' . time(),
            'SITE_ID' => $siteId
        ];

        $addedItemResult = $this->pageService->add($pageFields);
        $pageId = $addedItemResult->getId();
        $this->createdPageIds[] = $pageId;

        // Update the page
        $newTitle = 'Updated Page Title ' . time();
        $updatedItemResult = $this->pageService->update($pageId, [
            'TITLE' => $newTitle
        ]);

        self::assertTrue($updatedItemResult->isSuccess());

        // Verify the update
        $pagesResult = $this->pageService->getList(['ID', 'TITLE'], ['ID' => $pageId]);
        $pages = $pagesResult->getPages();

        self::assertCount(1, $pages);
        self::assertEquals($newTitle, $pages[0]->TITLE);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCopy(): void
    {
        $siteId = $this->createTestSite();

        // Create a test page
        $pageFields = [
            'TITLE' => 'Test Page for Copy ' . time(),
            'CODE' => 'testpagecopy' . time(),
            'SITE_ID' => $siteId
        ];

        $addedItemResult = $this->pageService->add($pageFields);
        $originalPageId = $addedItemResult->getId();
        $this->createdPageIds[] = $originalPageId;

        // Copy the page
        $copyResult = $this->pageService->copy($originalPageId);
        $copiedPageId = $copyResult->getId();
        $this->createdPageIds[] = $copiedPageId;

        self::assertGreaterThan(0, $copiedPageId);
        self::assertNotEquals($originalPageId, $copiedPageId);

        // Verify both pages exist
        $pagesResult = $this->pageService->getList(['ID', 'TITLE'], ['SITE_ID' => $siteId]);
        $pages = $pagesResult->getPages();

        $pageIds = array_map(fn($page): int => intval($page->ID), $pages);
        self::assertContains($originalPageId, $pageIds);
        self::assertContains($copiedPageId, $pageIds);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetAdditionalFields(): void
    {
        $siteId = $this->createTestSite();

        // Create a test page with additional fields
        $pageFields = [
            'TITLE' => 'Test Page Additional Fields ' . time(),
            'CODE' => 'testpageadditional' . time(),
            'SITE_ID' => $siteId,
            'ADDITIONAL_FIELDS' => [
                'THEME_CODE' => 'wedding',
                'METAMAIN_TITLE' => 'Test Meta Title'
            ]
        ];

        $addedItemResult = $this->pageService->add($pageFields);
        $pageId = $addedItemResult->getId();
        $this->createdPageIds[] = $pageId;

        // Get additional fields
        $pageAdditionalFieldsResult = $this->pageService->getAdditionalFields($pageId);
        $additionalFields = $pageAdditionalFieldsResult->getAdditionalFields();

        self::assertIsArray($additionalFields);
        // Note: The exact structure depends on API response format
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetPreview(): void
    {
        $siteId = $this->createTestSite();

        // Create a test page
        $pageFields = [
            'TITLE' => 'Test Page Preview ' . time(),
            'CODE' => 'testpagepreview' . time(),
            'SITE_ID' => $siteId
        ];

        $addedItemResult = $this->pageService->add($pageFields);
        $pageId = $addedItemResult->getId();
        $this->createdPageIds[] = $pageId;

        // Get preview
        $pagePreviewResult = $this->pageService->getPreview($pageId);
        $previewPath = $pagePreviewResult->getPreviewPath();

        self::assertIsString($previewPath);
        // Preview path might be empty or contain URL
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetPublicUrl(): void
    {
        $siteId = $this->createTestSite();

        // Create a test page
        $pageFields = [
            'TITLE' => 'Test Page Public URL ' . time(),
            'CODE' => 'testpagepublicurl' . time(),
            'SITE_ID' => $siteId
        ];

        $addedItemResult = $this->pageService->add($pageFields);
        $pageId = $addedItemResult->getId();
        $this->createdPageIds[] = $pageId;

        // Get public URL
        $pagePublicUrlResult = $this->pageService->getPublicUrl($pageId);
        $publicUrl = $pagePublicUrlResult->getPublicUrl();

        self::assertIsString($publicUrl);
        // Public URL might be empty until published
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testPublishAndUnpublish(): void
    {
        $siteId = $this->createTestSite();

        // Create a test page
        $pageFields = [
            'TITLE' => 'Test Page Publish ' . time(),
            'CODE' => 'testpagepublish' . time(),
            'SITE_ID' => $siteId
        ];

        $addedItemResult = $this->pageService->add($pageFields);
        $pageId = $addedItemResult->getId();
        $this->createdPageIds[] = $pageId;

        // Publish the page
        $updatedItemResult = $this->pageService->publish($pageId);
        self::assertTrue($updatedItemResult->isSuccess());

        // Unpublish the page
        $unpublishResult = $this->pageService->unpublish($pageId);
        self::assertTrue($unpublishResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testMarkDeletedAndUnDeleted(): void
    {
        $siteId = $this->createTestSite();

        // Create a test page
        $pageFields = [
            'TITLE' => 'Test Page Mark Delete ' . time(),
            'CODE' => 'testpagemarkdelete' . time(),
            'SITE_ID' => $siteId
        ];

        $addedItemResult = $this->pageService->add($pageFields);
        $pageId = $addedItemResult->getId();
        $this->createdPageIds[] = $pageId;

        // Mark as deleted
        $markPageDeletedResult = $this->pageService->markDeleted($pageId);
        self::assertTrue($markPageDeletedResult->isSuccess());

        // Mark as undeleted
        $markPageUnDeletedResult = $this->pageService->markUnDeleted($pageId);
        self::assertTrue($markPageUnDeletedResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testMove(): void
    {
        $sourceSiteId = $this->createTestSite();
        $targetSiteId = $this->createTestSite();

        // Create a test page
        $pageFields = [
            'TITLE' => 'Test Page Move ' . time(),
            'CODE' => 'testpagemove' . time(),
            'SITE_ID' => $sourceSiteId
        ];

        $addedItemResult = $this->pageService->add($pageFields);
        $pageId = $addedItemResult->getId();
        $this->createdPageIds[] = $pageId;

        // Move the page to another site
        $updatedItemResult = $this->pageService->move($pageId, $targetSiteId);
        self::assertTrue($updatedItemResult->isSuccess());

        // Verify the page is now in the target site
        $pagesResult = $this->pageService->getList(['ID', 'SITE_ID'], ['ID' => $pageId]);
        $pages = $pagesResult->getPages();

        self::assertCount(1, $pages);
        self::assertEquals($targetSiteId, $pages[0]->SITE_ID);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testRemoveEntities(): void
    {
        $siteId = $this->createTestSite();

        // Create a test page
        $pageFields = [
            'TITLE' => 'Test Page Remove Entities ' . time(),
            'CODE' => 'testpageremove' . time(),
            'SITE_ID' => $siteId
        ];

        $addedItemResult = $this->pageService->add($pageFields);
        $pageId = $addedItemResult->getId();
        $this->createdPageIds[] = $pageId;

        // Remove entities (empty data for test)
        $updatedItemResult = $this->pageService->removeEntities($pageId, [
            'blocks' => [],
            'images' => []
        ]);

        self::assertTrue($updatedItemResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testResolveIdByPublicUrl(): void
    {
        $siteId = $this->createTestSite();

        // Create a test page
        $timestamp = time();
        $pageCode = 'testpageresolve' . $timestamp;
        $pageFields = [
            'TITLE' => 'Test Page Resolve ' . $timestamp,
            'CODE' => $pageCode,
            'SITE_ID' => $siteId
        ];

        $addedItemResult = $this->pageService->add($pageFields);
        $pageId = $addedItemResult->getId();
        $this->createdPageIds[] = $pageId;

        // Try to resolve ID by URL
        $pageIdByUrlResult = $this->pageService->resolveIdByPublicUrl('/' . $pageCode . '/', $siteId);
        $resolvedPageId = $pageIdByUrlResult->getPageId();
        self::assertEquals($pageId, $resolvedPageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $siteId = $this->createTestSite();

        // Create a test page
        $pageFields = [
            'TITLE' => 'Test Page Delete ' . time(),
            'CODE' => 'testpagedelete' . time(),
            'SITE_ID' => $siteId
        ];

        $addedItemResult = $this->pageService->add($pageFields);
        $pageId = $addedItemResult->getId();

        // Delete the page
        $deletedItemResult = $this->pageService->delete($pageId);
        self::assertTrue($deletedItemResult->isSuccess());

        // Remove from cleanup list as it's already deleted
        $this->createdPageIds = array_filter($this->createdPageIds, fn($id): bool => $id !== $pageId);

        // Verify page is deleted by trying to get it
        $pagesResult = $this->pageService->getList(['ID'], ['ID' => $pageId]);
        $pages = $pagesResult->getPages();
        self::assertEmpty($pages, 'Page should be deleted and not found in list');
    }

    /**
     * Helper method to create a test page with blocks
     */
    protected function createTestPageWithBlocks(): int
    {
        $siteId = $this->createTestSite();

        $pageFields = [
            'TITLE' => 'Test Page for Blocks ' . time(),
            'CODE' => 'testpageblocks' . time(),
            'SITE_ID' => $siteId
        ];
        
        $addedItemResult = $this->pageService->add($pageFields);
        $pageId = $addedItemResult->getId();
        
        $this->createdPageIds[] = $pageId;

        return $pageId;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAddBlock(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        $blockFields = [
            'CODE' => '01.big_with_text_blocks',
            'ACTIVE' => 'Y'
        ];

        $addedItemResult = $this->pageService->addBlock($pageId, $blockFields);
        $blockId = $addedItemResult->getId();

        self::assertGreaterThan(0, $blockId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCopyBlock(): void
    {
        $pageId1 = $this->createTestPageWithBlocks();
        $pageId2 = $this->createTestPageWithBlocks();

        // First add a block to the first page
        $blockFields = [
            'CODE' => '01.big_with_text_blocks',
            'ACTIVE' => 'Y'
        ];

        $addedItemResult = $this->pageService->addBlock($pageId1, $blockFields);
        $originalBlockId = $addedItemResult->getId();

        // Copy the block to the second page
        $blockCopiedResult = $this->pageService->copyBlock($pageId2, $originalBlockId);
        $copiedBlockId = $blockCopiedResult->getId();

        self::assertGreaterThan(0, $copiedBlockId);
        self::assertNotEquals($originalBlockId, $copiedBlockId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testHideAndShowBlock(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Add a block
        $blockFields = [
            'CODE' => '01.big_with_text_blocks',
            'ACTIVE' => 'Y'
        ];

        $addedItemResult = $this->pageService->addBlock($pageId, $blockFields);
        $blockId = $addedItemResult->getId();

        // Hide the block
        $updatedItemResult = $this->pageService->hideBlock($pageId, $blockId);
        self::assertTrue($updatedItemResult->isSuccess());

        // Show the block
        $showResult = $this->pageService->showBlock($pageId, $blockId);
        self::assertTrue($showResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testMoveBlockUpAndDown(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Add two blocks
        $blockFields1 = [
            'CODE' => '01.big_with_text_blocks',
            'ACTIVE' => 'Y'
        ];

        $addedItemResult = $this->pageService->addBlock($pageId, $blockFields1);
        $block1Id = $addedItemResult->getId();

        $blockFields2 = [
            //'CODE' => '02.three_cols_text_big',
            'CODE' => '02.three_cols_big_1',
            'ACTIVE' => 'Y',
            'AFTER_ID' => $block1Id,
        ];
        
        $block2Result = $this->pageService->addBlock($pageId, $blockFields2);
        $block2Result->getId();
        
        // Move first block down
        $blockMovedResult = $this->pageService->moveBlockDown($pageId, $block1Id);
        self::assertTrue($blockMovedResult->isSuccess());

        
        // Move second block up
        $moveUpResult = $this->pageService->moveBlockUp($pageId, $block1Id);
        self::assertTrue($moveUpResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testMoveBlockBetweenPages(): void
    {
        $pageId1 = $this->createTestPageWithBlocks();
        $pageId2 = $this->createTestPageWithBlocks();

        // Add a block to the first page
        $blockFields = [
            'CODE' => '01.big_with_text_blocks',
            'ACTIVE' => 'Y'
        ];

        $addedItemResult = $this->pageService->addBlock($pageId1, $blockFields);
        $blockId = $addedItemResult->getId();

        // Move the block to the second page
        $blockMovedResult = $this->pageService->moveBlock($pageId2, $blockId);
        self::assertTrue($blockMovedResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testMarkBlockDeletedAndUnDeleted(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Add a block
        $blockFields = [
            'CODE' => '01.big_with_text_blocks',
            'ACTIVE' => 'Y'
        ];

        $addedItemResult = $this->pageService->addBlock($pageId, $blockFields);
        $blockId = $addedItemResult->getId();

        // Mark block as deleted
        $updatedItemResult = $this->pageService->markBlockDeleted($pageId, $blockId);
        self::assertTrue($updatedItemResult->isSuccess());

        // Mark block as undeleted
        $markUnDeletedResult = $this->pageService->markBlockUnDeleted($pageId, $blockId);
        self::assertTrue($markUnDeletedResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAddAndRemoveBlockFromFavorites(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Add a block
        $blockFields = [
            'CODE' => '01.big_with_text_blocks',
            'ACTIVE' => 'Y'
        ];

        $addedItemResult = $this->pageService->addBlock($pageId, $blockFields);
        $blockId = $addedItemResult->getId();

        // Add block to favorites
        $meta = [
            'name' => 'Test Favorite Block',
            'section' => ['text'],
            'preview' => 'https://example.com/preview.jpg'
        ];

        $favoriteResult = $this->pageService->addBlockToFavorites($pageId, $blockId, $meta);
        $favoriteBlockId = $favoriteResult->getId();

        // Verify it was added (should return a number)
        self::assertGreaterThan(0, $favoriteBlockId);

        // Remove block from favorites
        $updatedItemResult = $this->pageService->removeBlockFromFavorites($favoriteBlockId);
        self::assertTrue($updatedItemResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDeleteBlock(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Add a block
        $blockFields = [
            'CODE' => '01.big_with_text_blocks',
            'ACTIVE' => 'Y'
        ];

        $addedItemResult = $this->pageService->addBlock($pageId, $blockFields);
        $blockId = $addedItemResult->getId();

        // Delete the block
        $deletedItemResult = $this->pageService->deleteBlock($pageId, $blockId);
        self::assertTrue($deletedItemResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCopyBlockWithParameters(): void
    {
        $pageId1 = $this->createTestPageWithBlocks();
        $pageId2 = $this->createTestPageWithBlocks();

        // Add two blocks to the first page
        $blockFields1 = [
            'CODE' => '01.big_with_text_blocks',
            'ACTIVE' => 'Y'
        ];


        $addedItemResult = $this->pageService->addBlock($pageId1, $blockFields1);
        $block1Id = $addedItemResult->getId();

        $blockFields2 = [
            'CODE' => '02.three_cols_big_1',
            'ACTIVE' => 'Y',
            'AFTER_ID' => $block1Id,
        ];
        $block2Result = $this->pageService->addBlock($pageId1, $blockFields2);
        $block2Id = $block2Result->getId();

        // Copy block with AFTER_ID parameter
        $params = ['AFTER_ID' => $block1Id];
        $blockCopiedResult = $this->pageService->copyBlock($pageId2, $block2Id, $params);
        $copiedBlockId = $blockCopiedResult->getId();

        self::assertGreaterThan(0, $copiedBlockId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testMoveBlockWithParameters(): void
    {
        $pageId1 = $this->createTestPageWithBlocks();
        $pageId2 = $this->createTestPageWithBlocks();

        // Add two blocks to the first page
        $blockFields1 = [
            'CODE' => '01.big_with_text_blocks',
            'ACTIVE' => 'Y'
        ];


        $addedItemResult = $this->pageService->addBlock($pageId1, $blockFields1);
        $block1Id = $addedItemResult->getId();

        $blockFields2 = [
            'CODE' => '02.three_cols_big_1',
            'ACTIVE' => 'Y',
            'AFTER_ID' => $block1Id,
        ];
        $block2Result = $this->pageService->addBlock($pageId1, $blockFields2);
        $block2Id = $block2Result->getId();

        // Add a block to the second page to use as reference
        $block3Result = $this->pageService->addBlock($pageId2, $blockFields1);
        $block3Id = $block3Result->getId();

        // Move block with AFTER_ID parameter
        $params = ['AFTER_ID' => $block3Id];
        $blockMovedResult = $this->pageService->moveBlock($pageId2, $block2Id, $params);
        self::assertTrue($blockMovedResult->isSuccess());
    }
}