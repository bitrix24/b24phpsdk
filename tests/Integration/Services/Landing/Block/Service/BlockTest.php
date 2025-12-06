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
     * Helper method to add a text block to page if no suitable blocks exist
     */
    protected function ensurePageHasTextBlock(int $pageId): array
    {
        // First try to find existing block with nodes
        $blockWithNodes = $this->findBlockWithNodes($pageId);
        if ($blockWithNodes !== null) {
            return $blockWithNodes;
        }

        // If no suitable block found, use any first block and fallback to standard selectors
        $blocksResult = $this->blockService->list($pageId, ['edit_mode' => 1]);
        $blocks = $blocksResult->getBlocks();
        
        if ($blocks !== []) {
            return [
                'blockId' => $blocks[0]->id,
                'nodeSelectors' => ['.landing-block-node-text'],
                'manifest' => null
            ];
        }

        // This should never happen with template pages, but just in case
        throw new \RuntimeException('No blocks found on page and unable to add new blocks');
    }

    /**
     * Helper method to add a card block to page if no suitable blocks exist
     */
    protected function ensurePageHasCardBlock(int $pageId): array
    {
        // First try to find existing block with cards
        $blockWithCards = $this->findBlockWithCards($pageId);
        if ($blockWithCards !== null) {
            return $blockWithCards;
        }

        // If no suitable block found, use any first block and fallback to standard selectors
        $blocksResult = $this->blockService->list($pageId, ['edit_mode' => 1]);
        $blocks = $blocksResult->getBlocks();
        
        if ($blocks !== []) {
            return [
                'blockId' => $blocks[0]->id,
                'cardSelector' => '.landing-block-card',
                'manifest' => null
            ];
        }

        // This should never happen with template pages, but just in case
        throw new \RuntimeException('No blocks found on page and unable to add new blocks');
    }

    /**
     * Helper method to find a block with cards from its manifest
     */
    protected function findBlockWithCards(int $pageId): ?array
    {
        // Get blocks list for the page
        $blocksResult = $this->blockService->list($pageId, ['edit_mode' => 1]);
        $blocks = $blocksResult->getBlocks();

        foreach ($blocks as $block) {
            try {
                // Get manifest for this block
                $manifestResult = $this->blockService->getManifest($pageId, $block->id, ['edit_mode' => 1]);
                $manifest = $manifestResult->getManifest();

                // Check if block has cards in manifest
                if (!empty($manifest->cards) && is_array($manifest->cards)) {
                    // Return first card selector found
                    $cardSelector = key($manifest->cards);
                    if ($cardSelector !== 0 && ($cardSelector !== '' && $cardSelector !== '0')) {
                        return [
                            'blockId' => $block->id,
                            'cardSelector' => $cardSelector,
                            'manifest' => $manifest
                        ];
                    }
                }
            } catch (\Exception) {
                // Skip blocks that can't provide manifest or don't have cards
                continue;
            }
        }

        return null;
    }

    /**
     * Helper method to find a block with specific node selectors
     */
    protected function findBlockWithNodes(int $pageId, array $requiredNodeTypes = []): ?array
    {
        // Get blocks list for the page
        $blocksResult = $this->blockService->list($pageId, ['edit_mode' => 1]);
        $blocks = $blocksResult->getBlocks();

        foreach ($blocks as $block) {
            try {
                // Get manifest for this block
                $manifestResult = $this->blockService->getManifest($pageId, $block->id, ['edit_mode' => 1]);
                $manifest = $manifestResult->getManifest();

                // Check if block has nodes in manifest
                if (!empty($manifest->nodes) && is_array($manifest->nodes)) {
                    $nodeSelectors = array_keys($manifest->nodes);
                    
                    // If specific node types required, check for them
                    if ($requiredNodeTypes !== []) {
                        $hasRequiredNodes = false;
                        foreach ($nodeSelectors as $nodeSelector) {
                            foreach ($requiredNodeTypes as $requiredNodeType) {
                                if (str_contains($nodeSelector, (string) $requiredNodeType)) {
                                    $hasRequiredNodes = true;
                                    break 2;
                                }
                            }
                        }

                        if (!$hasRequiredNodes) {
                            continue;
                        }
                    }

                    return [
                        'blockId' => $block->id,
                        'nodeSelectors' => $nodeSelectors,
                        'manifest' => $manifest
                    ];
                }
            } catch (\Exception) {
                // Skip blocks that can't provide manifest
                continue;
            }
        }

        return null;
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

        // Ensure page has a block with text nodes (find existing or create new)
        $blockWithNodes = $this->ensurePageHasTextBlock($pageId);
        
        $blockId = $blockWithNodes['blockId'];
        $nodeSelectors = $blockWithNodes['nodeSelectors'];

        // Use first available node selector
        $firstNodeSelector = $nodeSelectors[0];

        // Test updating block nodes with real selector
        $updateData = [
            $firstNodeSelector => 'Updated text content ' . time()
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

        // Ensure page has a block with text nodes (find existing or create new)
        $blockWithNodes = $this->ensurePageHasTextBlock($pageId);
        
        $blockId = $blockWithNodes['blockId'];
        $nodeSelectors = $blockWithNodes['nodeSelectors'];

        // Use first available node selector
        $firstNodeSelector = $nodeSelectors[0];

        // Test updating block styles with real selector
        $styleData = [
            $firstNodeSelector => [
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

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdateAttrs(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Ensure page has a block with text nodes (find existing or create new)
        $blockWithNodes = $this->ensurePageHasTextBlock($pageId);
        
        $blockId = $blockWithNodes['blockId'];
        $nodeSelectors = $blockWithNodes['nodeSelectors'];

        // Use first available node selector
        $firstNodeSelector = $nodeSelectors[0];

        // Test updating block attributes with real selector
        $attrsData = [
            $firstNodeSelector => [
                'href' => 'https://example.com',
                'target' => '_blank'
            ]
        ];

        $updateResult = $this->blockService->updateAttrs($pageId, $blockId, $attrsData);
        $isSuccess = $updateResult->isSuccess();

        self::assertTrue($isSuccess);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdateContent(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Get blocks list first to get a block ID
        $blocksResult = $this->blockService->list($pageId, ['edit_mode' => 1]);
        $blocks = $blocksResult->getBlocks();

        self::assertNotEmpty($blocks, 'Page must have blocks for this test');

        $blockId = $blocks[0]->id;

        // Test updating block content with arbitrary content
        $newContent = '<div class="test-content">Updated content ' . time() . '</div>';

        $updateResult = $this->blockService->updateContent($pageId, $blockId, $newContent);
        $isSuccess = $updateResult->isSuccess();

        self::assertTrue($isSuccess);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdateCards(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Try to find a block with cards, fallback to text block
        $blockWithCards = $this->findBlockWithCards($pageId);
        
        if ($blockWithCards !== null) {
            $blockId = $blockWithCards['blockId'];
            $cardSelector = $blockWithCards['cardSelector'];
        } else {
            // Fallback: use text block and try card operations
            $blockWithNodes = $this->ensurePageHasTextBlock($pageId);
            $blockId = $blockWithNodes['blockId'];
            $cardSelector = '.landing-block-card';
        }

        // Get a node selector
        $blockWithNodes = $this->ensurePageHasTextBlock($pageId);
        $nodeSelector = $blockWithNodes['nodeSelectors'][0];

        // Test bulk updating block cards with selectors
        $cardsData = [
            $cardSelector . '@0' => [
                $nodeSelector => 'Updated card text ' . time()
            ]
        ];

        // This may fail if block doesn't support cards, but that's ok for testing
        $updateResult = $this->blockService->updateCards($pageId, $blockId, $cardsData);
        $isSuccess = $updateResult->isSuccess();
        self::assertTrue($isSuccess);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCloneCard(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Try to find a block with cards, fallback to any block
        $blockWithCards = $this->findBlockWithCards($pageId);
        
        if ($blockWithCards !== null) {
            $blockId = $blockWithCards['blockId'];
            $cardSelector = $blockWithCards['cardSelector'];
        } else {
            // Fallback: use any block and try card operations
            $blockWithNodes = $this->ensurePageHasTextBlock($pageId);
            $blockId = $blockWithNodes['blockId'];
            $cardSelector = '.landing-block-card';
        }

        // Test cloning a block card - this may fail if block doesn't support cards
        $updateResult = $this->blockService->cloneCard($pageId, $blockId, $cardSelector);
        $isSuccess = $updateResult->isSuccess();
        self::assertTrue($isSuccess);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAddCard(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Try to find a block with cards, fallback to any block
        $blockWithCards = $this->findBlockWithCards($pageId);
        
        if ($blockWithCards !== null) {
            $blockId = $blockWithCards['blockId'];
            $cardSelector = $blockWithCards['cardSelector'];
        } else {
            // Fallback: use any block and try card operations
            $blockWithNodes = $this->ensurePageHasTextBlock($pageId);
            $blockId = $blockWithNodes['blockId'];
            $cardSelector = '.landing-block-card';
        }

        // Test adding a card with modified content
        $content = '<div>New card content ' . time() . '</div>';

        // This may fail if block doesn't support cards, but that's ok for testing
        $updateResult = $this->blockService->addCard($pageId, $blockId, $cardSelector, $content);
        $isSuccess = $updateResult->isSuccess();
        self::assertTrue($isSuccess);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testRemoveCard(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Try to find a block with cards, fallback to any block
        $blockWithCards = $this->findBlockWithCards($pageId);
        
        if ($blockWithCards !== null) {
            $blockId = $blockWithCards['blockId'];
            $cardSelector = $blockWithCards['cardSelector'];
            
            // First clone a card to have something to remove
            $this->blockService->cloneCard($pageId, $blockId, $cardSelector);
            
            // Test removing a block card - target the cloned card (should be index 1)
            $removeSelector = $cardSelector . '@1';
            $updateResult = $this->blockService->removeCard($pageId, $blockId, $removeSelector);
            $isSuccess = $updateResult->isSuccess();
            self::assertTrue($isSuccess);
            
            return;
        } 

        // Fallback: use any block and try card operations
        $blockWithNodes = $this->ensurePageHasTextBlock($pageId);
        $blockId = $blockWithNodes['blockId'];
        $cardSelector = '.landing-block-card';

        // Test removing a card - this may fail if block doesn't support cards
        $removeSelector = $cardSelector . '@0';
        $updateResult = $this->blockService->removeCard($pageId, $blockId, $removeSelector);
        $isSuccess = $updateResult->isSuccess();
        self::assertTrue($isSuccess);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testChangeNodeName(): void
    {
        $pageId = $this->createTestPageWithBlocks();

        // Ensure page has a block with text nodes (find existing or create new)
        $blockWithNodes = $this->ensurePageHasTextBlock($pageId);
        
        $blockId = $blockWithNodes['blockId'];
        $nodeSelectors = $blockWithNodes['nodeSelectors'];

        // Use first available node selector
        $firstNodeSelector = $nodeSelectors[0];

        // Test changing tag name using data array format with real selector
        $data = [
            $firstNodeSelector => 'h2'
        ];

        $updateResult = $this->blockService->changeNodeName($pageId, $blockId, $data);
        $isSuccess = $updateResult->isSuccess();

        self::assertTrue($isSuccess);
    }
}
