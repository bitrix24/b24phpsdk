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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\Block\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\Block\Result\BlockContentItemResult;
use Bitrix24\SDK\Services\Landing\Block\Result\BlockItemResult;
use Bitrix24\SDK\Services\Landing\Block\Result\BlockManifestItemResult;
use Bitrix24\SDK\Services\Landing\Block\Service\Block;
use Bitrix24\SDK\Services\Landing\Page\Service\Page;
use Bitrix24\SDK\Services\Landing\Site\Service\Site;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class BlockTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Landing\Block\Service
 */
#[CoversMethod(Block::class, 'list')]
#[CoversMethod(Block::class, 'getById')]
#[CoversMethod(Block::class, 'getContent')]
#[CoversMethod(Block::class, 'getManifest')]
#[CoversMethod(Block::class, 'getRepository')]
#[CoversMethod(Block::class, 'getManifestFile')]
#[CoversMethod(Block::class, 'getContentFromRepository')]
#[CoversMethod(Block::class, 'updateNodes')]
#[CoversMethod(Block::class, 'updateAttrs')]
#[CoversMethod(Block::class, 'updateStyles')]
#[CoversMethod(Block::class, 'updateContent')]
#[CoversMethod(Block::class, 'updateCards')]
#[CoversMethod(Block::class, 'cloneCard')]
#[CoversMethod(Block::class, 'addCard')]
#[CoversMethod(Block::class, 'removeCard')]
#[CoversMethod(Block::class, 'uploadFile')]
#[CoversMethod(Block::class, 'changeAnchor')]
#[CoversMethod(Block::class, 'changeNodeName')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Landing\Block\Service\Block::class)]
class BlockTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Block $blockService;

    protected Page $pageService;

    protected Site $siteService;

    protected array $createdPageIds = [];

    protected array $createdSiteIds = [];

    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->blockService = $serviceBuilder->getLandingScope()->block();
        $this->pageService = $serviceBuilder->getLandingScope()->page();
        $this->siteService = $serviceBuilder->getLandingScope()->site();
    }

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
            'TITLE' => 'Test Site for Block ' . time(),
            'CODE' => 'testsiteblock' . time(),
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        return $siteId;
    }

    /**
     * Helper method to create a test page with blocks
     */
    protected function createTestPageWithBlocks(): int
    {
        $siteId = $this->createTestSite();

        // Get available page templates
        $core = Fabric::getCore();
        $templatesResponse = $core->call('landing.demos.getPageList', ['type' => 'page']);
        $templates = $templatesResponse->getResponseData()->getResult();

        // Use the first available template to get a page with blocks
        $templateCode = key($templates);

        $addedItemResult = $this->pageService->addByTemplate(
            $siteId,
            $templateCode,
            [
                'TITLE' => 'Test Page with Blocks ' . time(),
                'DESCRIPTION' => 'Test page for block operations'
            ]
        );

        $pageId = $addedItemResult->getId();
        $this->createdPageIds[] = $pageId;

        return $pageId;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Test getting blocks list for the page
        $blocksResult = $this->blockService->list($pageId, ['edit_mode' => 1]);
        $blocks = $blocksResult->getBlocks();

        self::assertIsArray($blocks);

        if ($blocks !== []) {
            $firstBlock = $blocks[0];
            self::assertInstanceOf(BlockItemResult::class, $firstBlock);
            self::assertGreaterThan(0, $firstBlock->id);
            self::assertEquals($pageId, $firstBlock->lid);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetById(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Get blocks list first to get a block ID
        $blocksResult = $this->blockService->list($pageId, ['edit_mode' => 1]);
        $blocks = $blocksResult->getBlocks();

        self::assertNotEmpty($blocks, 'Page must have blocks for this test');

        $blockId = $blocks[0]->id;

        // Test getting block by ID
        $blockResult = $this->blockService->getById($blockId, ['edit_mode' => 1]);
        $blockItemResult = $blockResult->getBlock();

        self::assertInstanceOf(BlockItemResult::class, $blockItemResult);
        self::assertEquals($blockId, $blockItemResult->id);
        self::assertEquals($pageId, $blockItemResult->lid);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetContent(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Get blocks list first to get a block ID
        $blocksResult = $this->blockService->list($pageId, ['edit_mode' => 1]);
        $blocks = $blocksResult->getBlocks();

        self::assertNotEmpty($blocks, 'Page must have blocks for this test');

        $blockId = $blocks[0]->id;

        // Test getting block content
        $blockContentResult = $this->blockService->getContent($pageId, $blockId, 1, ['wrapper_show' => 1]);
        $blockContentItemResult = $blockContentResult->getContent();

        // Check that a typed object is returned
        self::assertInstanceOf(BlockContentItemResult::class, $blockContentItemResult);

    // Check required fields
    self::assertIsInt($blockContentItemResult->id);
    self::assertIsString($blockContentItemResult->sections);
    self::assertIsBool($blockContentItemResult->active);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetManifest(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Get blocks list first to get a block ID
        $blocksResult = $this->blockService->list($pageId, ['edit_mode' => 1]);
        $blocks = $blocksResult->getBlocks();

        self::assertNotEmpty($blocks, 'Page must have blocks for this test');

        $blockId = $blocks[0]->id;

        // Test getting block manifest
        $blockManifestResult = $this->blockService->getManifest($pageId, $blockId, ['edit_mode' => 1]);
        $blockManifestItemResult = $blockManifestResult->getManifest();

        // Check that a typed object is returned
        self::assertInstanceOf(BlockManifestItemResult::class, $blockManifestItemResult);

        // Check required fields
    self::assertIsArray($blockManifestItemResult->block);
    self::assertIsArray($blockManifestItemResult->cards);
    self::assertIsArray($blockManifestItemResult->nodes);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetRepository(): void
    {
        // Test getting blocks from repository
        $repositoryResult = $this->blockService->getRepository('about');
        $repositoryItemResult = $repositoryResult->getRepository();

        self::assertInstanceOf(\Bitrix24\SDK\Services\Landing\Block\Result\RepositoryItemResult::class, $repositoryItemResult);
        self::assertNotEmpty($repositoryItemResult->name, 'Repository name must not be empty');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetManifestFile(): void
    {
        // Get repository blocks
        $repositoryResult = $this->blockService->getRepository('about');
        $repositoryItemResult = $repositoryResult->getRepository();

        self::assertInstanceOf(\Bitrix24\SDK\Services\Landing\Block\Result\RepositoryItemResult::class, $repositoryItemResult);

        // Get block code from first item in the repository
        self::assertNotEmpty($repositoryItemResult->items, 'Repository must have items');
        $blockCode = key($repositoryItemResult->items);
        if ($blockCode === null) {
            $blockCode = 'bitrix:landing.blocks.html_text';
        }

        // Test getting manifest from repository
        $blockManifestResult = $this->blockService->getManifestFile($blockCode);
        $blockManifestItemResult = $blockManifestResult->getManifest();

        self::assertInstanceOf(\Bitrix24\SDK\Services\Landing\Block\Result\BlockManifestItemResult::class, $blockManifestItemResult);
        self::assertNotEmpty($blockManifestItemResult->block, 'Manifest block must not be empty');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetContentFromRepository(): void
    {
        // Get repository blocks first
        $repositoryResult = $this->blockService->getRepository('about');
        $repositoryItemResult = $repositoryResult->getRepository();

        self::assertInstanceOf(\Bitrix24\SDK\Services\Landing\Block\Result\RepositoryItemResult::class, $repositoryItemResult);

        // Get block code from first item in the repository
        self::assertNotEmpty($repositoryItemResult->items, 'Repository must have items');
        $blockCode = key($repositoryItemResult->items);
        if ($blockCode === null) {
            $blockCode = 'bitrix:landing.blocks.html_text';
        }

        // Test getting content from repository
        $repositoryContentResult = $this->blockService->getContentFromRepository($blockCode);
        $content = $repositoryContentResult->getContent();

        self::assertIsString($content);
        self::assertNotEmpty($content, 'Content must not be empty');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdateNodes(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Get blocks list first to get a block ID
        $blocksResult = $this->blockService->list($pageId, ['edit_mode' => 1]);
        $blocks = $blocksResult->getBlocks();

        self::assertNotEmpty($blocks, 'Page must have blocks for this test');

        $blockId = $blocks[0]->id;

        // Test updating block nodes
        $updateData = [
            '.landing-block-node-text' => 'Updated text content ' . time()
        ];

        $updateResult = $this->blockService->updateNodes($pageId, $blockId, $updateData);
        $isSuccess = $updateResult->isSuccess();

        self::assertTrue($isSuccess);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdateStyles(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Get blocks list first to get a block ID
        $blocksResult = $this->blockService->list($pageId, ['edit_mode' => 1]);
        $blocks = $blocksResult->getBlocks();

        self::assertNotEmpty($blocks, 'Page must have blocks for this test');

        $blockId = $blocks[0]->id;

        // Test updating block styles
        $styleData = [
            '.landing-block-node-text' => [
                'classList' => ['landing-block-node-text', 'g-color-primary'],
                'affect' => ['color']
            ]
        ];

        $updateResult = $this->blockService->updateStyles($pageId, $blockId, $styleData);
        $isSuccess = $updateResult->isSuccess();

        self::assertTrue($isSuccess);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUploadFile(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Get blocks list first to get a block ID
        $blocksResult = $this->blockService->list($pageId, ['edit_mode' => 1]);
        $blocks = $blocksResult->getBlocks();

        self::assertNotEmpty($blocks, 'Page must have blocks for this test');

        $blockId = $blocks[0]->id;

        // Test uploading a file (using a simple base64 encoded 1x1 pixel image)
        $imageData = [
            'test.png',
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAI9jgvKNQAAAABJRU5ErkJggg=='
        ];

        $uploadFileResult = $this->blockService->uploadFile($blockId, $imageData);

        // Check new typed methods
        self::assertIsInt($uploadFileResult->getId());
        self::assertIsString($uploadFileResult->getUrl());
        self::assertNotEmpty($uploadFileResult->getUrl());

        // Check deprecated methods for backward compatibility
        self::assertEquals($uploadFileResult->getId(), $uploadFileResult->getFileId());
        self::assertEquals($uploadFileResult->getUrl(), $uploadFileResult->getFilePath());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testChangeAnchor(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Get blocks list first to get a block ID
        $blocksResult = $this->blockService->list($pageId, ['edit_mode' => 1]);
        $blocks = $blocksResult->getBlocks();

        self::assertNotEmpty($blocks, 'Page must have blocks for this test');

        $blockId = $blocks[0]->id;

        // Test changing anchor
        $newAnchor = 'test-anchor-' . time();
        $updateResult = $this->blockService->changeAnchor($pageId, $blockId, $newAnchor);
        $isSuccess = $updateResult->isSuccess();

        self::assertTrue($isSuccess);
    }
}
