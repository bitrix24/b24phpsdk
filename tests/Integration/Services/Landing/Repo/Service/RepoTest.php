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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\Repo\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\Repo\Result\RepoItemResult;
use Bitrix24\SDK\Services\Landing\Repo\Service\Repo;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class RepoTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Landing\Repo\Service
 */
#[CoversMethod(Repo::class, 'getList')]
#[CoversMethod(Repo::class, 'register')]
#[CoversMethod(Repo::class, 'unregister')]
#[CoversMethod(Repo::class, 'checkContent')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Landing\Repo\Service\Repo::class)]
class RepoTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Repo $repoService;

    protected array $createdBlockCodes = [];

    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->repoService = $serviceBuilder->getLandingScope()->repo();
    }

    protected function tearDown(): void
    {
        // Clean up created blocks
        foreach ($this->createdBlockCodes as $createdBlockCode) {
            try {
                $this->repoService->unregister($createdBlockCode);
            } catch (\Exception) {
                // Ignore if block doesn't exist
            }
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetList(): void
    {
        // First create a test block to ensure we have something in the list
        $timestamp = time();
        $blockCode = 'test_block_list_' . $timestamp;
        
        $fields = [
            'NAME' => 'Test Block for List ' . $timestamp,
            'DESCRIPTION' => 'Test block description',
            'SECTIONS' => 'text,content',
            'PREVIEW' => 'https://example.com/preview.png',
            'CONTENT' => '<div class="test-block">Test Content</div>',
            'ACTIVE' => 'Y'
        ];
        
        $manifest = [
            'block' => [
                'name' => 'Test Block',
                'section' => ['text']
            ]
        ];
        
        $addedItemResult = $this->repoService->register($blockCode, $fields, $manifest);
        $blockId = $addedItemResult->getId();
        $this->createdBlockCodes[] = $blockCode;
        
        self::assertGreaterThan(0, $blockId);

        // Test getList with no parameters
        $repoGetListResult = $this->repoService->getList();
        $blocks = $repoGetListResult->getRepoItems();

        self::assertIsArray($blocks);
        self::assertNotEmpty($blocks);

        // Check that our created block is in the list
        $foundBlock = null;
        foreach ($blocks as $block) {
            self::assertInstanceOf(RepoItemResult::class, $block);
            if ((int)$block->ID === $blockId) {
                $foundBlock = $block;
                break;
            }
        }

        self::assertNotNull($foundBlock, 'Created block should be found in the list');
        self::assertEquals($fields['NAME'], $foundBlock->NAME);
        self::assertEquals($fields['ACTIVE'], $foundBlock->ACTIVE);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetListWithFilters(): void
    {
        // Create a test block
        $timestamp = time();
        $blockCode = 'test_block_filter_' . $timestamp;
        
        $fields = [
            'NAME' => 'Test Block Filter ' . $timestamp,
            'DESCRIPTION' => 'Test block description for filtering',
            'SECTIONS' => 'text',
            'PREVIEW' => 'https://example.com/preview-filter.png',
            'CONTENT' => '<div class="test-block-filter">Filter Test Content</div>',
            'ACTIVE' => 'Y'
        ];
        
        $manifest = [
            'block' => [
                'name' => 'Test Filter Block',
                'section' => ['text']
            ]
        ];
        
        $addedItemResult = $this->repoService->register($blockCode, $fields, $manifest);
        $blockId = $addedItemResult->getId();
        $this->createdBlockCodes[] = $blockCode;

        // Test getList with filters
        $repoGetListResult = $this->repoService->getList(
            ['ID', 'NAME', 'ACTIVE'],
            ['ID' => $blockId],
            ['ID' => 'DESC']
        );

        $blocks = $repoGetListResult->getRepoItems();

        self::assertIsArray($blocks);
        self::assertCount(1, $blocks);
        
        $block = $blocks[0];
        self::assertEquals($blockId, (int)$block->ID);
        self::assertEquals($fields['NAME'], $block->NAME);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testRegister(): void
    {
        $timestamp = time();
        $blockCode = 'test_block_register_' . $timestamp;
        
        $fields = [
            'NAME' => 'Test Block Register ' . $timestamp,
            'DESCRIPTION' => 'Test block description for registration',
            'SECTIONS' => 'content,business',
            'PREVIEW' => 'https://example.com/preview-register.png',
            'CONTENT' => '<div class="test-register-block"><h2>Test Register Content</h2><p>Some content here</p></div>',
            'ACTIVE' => 'Y'
        ];
        
        $manifest = [
            'block' => [
                'name' => 'Test Register Block',
                'section' => ['content', 'business'],
                'dynamic' => false
            ]
        ];
        
        $addedItemResult = $this->repoService->register($blockCode, $fields, $manifest);
        $blockId = $addedItemResult->getId();
        $this->createdBlockCodes[] = $blockCode;
        
        self::assertGreaterThan(0, $blockId);
        
        // Verify the block was created by getting the list
        $repoGetListResult = $this->repoService->getList(['ID', 'NAME'], ['ID' => $blockId]);
        $blocks = $repoGetListResult->getRepoItems();
        
        self::assertCount(1, $blocks);
        self::assertEquals($fields['NAME'], $blocks[0]->NAME);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testRegisterWithExistingCode(): void
    {
        $timestamp = time();
        $blockCode = 'test_block_duplicate_' . $timestamp;
        
        $fields1 = [
            'NAME' => 'First Block ' . $timestamp,
            'DESCRIPTION' => 'First block description',
            'SECTIONS' => 'text',
            'PREVIEW' => 'https://example.com/first-preview.png',
            'CONTENT' => '<div>First content</div>',
            'ACTIVE' => 'Y'
        ];
        
        $manifest1 = [
            'block' => [
                'name' => 'First Block',
                'section' => ['text']
            ]
        ];
        
        // Register first block
        $addedItemResult = $this->repoService->register($blockCode, $fields1, $manifest1);
        $blockId1 = $addedItemResult->getId();
        $this->createdBlockCodes[] = $blockCode;
        
        self::assertGreaterThan(0, $blockId1);
        
        $fields2 = [
            'NAME' => 'Second Block ' . $timestamp,
            'DESCRIPTION' => 'Second block description (replacement)',
            'SECTIONS' => 'business',
            'PREVIEW' => 'https://example.com/second-preview.png',
            'CONTENT' => '<div>Second content (replacement)</div>',
            'ACTIVE' => 'Y'
        ];
        
        $manifest2 = [
            'block' => [
                'name' => 'Second Block (Replacement)',
                'section' => ['business']
            ]
        ];
        
        // Register second block with same code (should replace the first one)
        $registerResult2 = $this->repoService->register($blockCode, $fields2, $manifest2);
        $blockId2 = $registerResult2->getId();
        
        self::assertGreaterThan(0, $blockId2);
        
        // Verify the second block replaced the first
        $repoGetListResult = $this->repoService->getList(['ID', 'NAME'], ['ID' => $blockId2]);
        $blocks = $repoGetListResult->getRepoItems();
        
        self::assertCount(1, $blocks);
        self::assertEquals($fields2['NAME'], $blocks[0]->NAME);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUnregister(): void
    {
        $timestamp = time();
        $blockCode = 'test_block_unregister_' . $timestamp;
        
        $fields = [
            'NAME' => 'Test Block Unregister ' . $timestamp,
            'DESCRIPTION' => 'Test block for unregistration',
            'SECTIONS' => 'text',
            'PREVIEW' => 'https://example.com/unregister-preview.png',
            'CONTENT' => '<div>Content to be unregistered</div>',
            'ACTIVE' => 'Y'
        ];
        
        $manifest = [
            'block' => [
                'name' => 'Test Unregister Block',
                'section' => ['text']
            ]
        ];
        
        // Register the block first
        $addedItemResult = $this->repoService->register($blockCode, $fields, $manifest);
        $blockId = $addedItemResult->getId();
        
        self::assertGreaterThan(0, $blockId);
        
        // Unregister the block
        $deletedItemResult = $this->repoService->unregister($blockCode);
        
        self::assertTrue($deletedItemResult->isSuccess());
        
        // Remove from cleanup list as it's already unregistered
        $this->createdBlockCodes = array_filter($this->createdBlockCodes, fn($code): bool => $code !== $blockCode);
        
        // Verify the block is no longer in the list
        $repoGetListResult = $this->repoService->getList(['ID'], ['ID' => $blockId]);
        $blocks = $repoGetListResult->getRepoItems();
        
        self::assertEmpty($blocks, 'Block should be unregistered and not found in list');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUnregisterNonExistentBlock(): void
    {
        $timestamp = time();
        $nonExistentCode = 'non_existent_block_' . $timestamp;
        
        // Try to unregister a block that doesn't exist
        $deletedItemResult = $this->repoService->unregister($nonExistentCode);
        
        // Should return false for non-existent block
        self::assertFalse($deletedItemResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCheckContentSafe(): void
    {
        $safeContent = '<div class="safe-block"><h2>Safe Title</h2><p>This is safe content</p></div>';
        
        $repoCheckContentResult = $this->repoService->checkContent($safeContent);
        
        self::assertFalse($repoCheckContentResult->isBad(), 'Safe content should not be marked as bad');
        self::assertEquals($safeContent, $repoCheckContentResult->getContent());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCheckContentDangerous(): void
    {
        $dangerousContent = '<div onclick="alert(\'danger\')" style="color: red"><iframe src="//evil.com"></iframe></div>';
        
        $repoCheckContentResult = $this->repoService->checkContent($dangerousContent);
        
        self::assertTrue($repoCheckContentResult->isBad(), 'Dangerous content should be marked as bad');
        
        $processedContent = $repoCheckContentResult->getContent();
        self::assertNotNull($processedContent);
        
        // The processed content should contain the sanitization markers
        self::assertStringContainsString('#SANITIZE#', $processedContent);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCheckContentWithCustomSplitter(): void
    {
        $dangerousContent = '<div onclick="alert(\'test\')" style="background: blue">Test</div>';
        $customSplitter = '#CUSTOM_SPLITTER#';
        
        $repoCheckContentResult = $this->repoService->checkContent($dangerousContent, $customSplitter);
        
        if ($repoCheckContentResult->isBad()) {
            $processedContent = $repoCheckContentResult->getContent();
            self::assertNotNull($processedContent);
            
            // The processed content should contain the custom sanitization markers
            self::assertStringContainsString($customSplitter, $processedContent);
            self::assertStringNotContainsString('#SANITIZE#', $processedContent);
        } else {
            // If content is not marked as bad, it should be unchanged
            self::assertEquals($dangerousContent, $repoCheckContentResult->getContent());
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testRegisterWithSanitizedContent(): void
    {
        $timestamp = time();
        $blockCode = 'test_block_sanitized_' . $timestamp;
        
        // First check if the content is safe
        $contentToCheck = '<div class="clean-block"><h3>Clean Content</h3><p>No dangerous elements here</p></div>';
        $repoCheckContentResult = $this->repoService->checkContent($contentToCheck);
        
        self::assertFalse($repoCheckContentResult->isBad(), 'Content should be safe');
        
        $fields = [
            'NAME' => 'Test Sanitized Block ' . $timestamp,
            'DESCRIPTION' => 'Block with pre-checked content',
            'SECTIONS' => 'content',
            'PREVIEW' => 'https://example.com/sanitized-preview.png',
            'CONTENT' => $repoCheckContentResult->getContent(),
            'ACTIVE' => 'Y'
        ];
        
        $manifest = [
            'block' => [
                'name' => 'Sanitized Block',
                'section' => ['content']
            ]
        ];
        
        $addedItemResult = $this->repoService->register($blockCode, $fields, $manifest);
        $blockId = $addedItemResult->getId();
        $this->createdBlockCodes[] = $blockCode;
        
        self::assertGreaterThan(0, $blockId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testRegisterWithComplexManifest(): void
    {
        $timestamp = time();
        $blockCode = 'test_block_complex_' . $timestamp;
        
        $fields = [
            'NAME' => 'Complex Test Block ' . $timestamp,
            'DESCRIPTION' => 'Block with complex manifest',
            'SECTIONS' => 'content,text,business',
            'PREVIEW' => 'https://example.com/complex-preview.png',
            'CONTENT' => '<div class="complex-block"><h1>Complex Block</h1><div class="content-area">Content area</div><div class="sidebar">Sidebar</div></div>',
            'ACTIVE' => 'Y'
        ];
        
        $manifest = [
            'block' => [
                'name' => 'Complex Test Block',
                'section' => ['content', 'text', 'business'],
                'dynamic' => true,
                'subtype' => 'text'
            ],
            'cards' => [
                [
                    'name' => 'Main Card',
                    'selector' => '.content-area'
                ]
            ],
            'nodes' => [
                'title' => [
                    'name' => 'Title',
                    'selector' => 'h1'
                ],
                'content' => [
                    'name' => 'Content',
                    'selector' => '.content-area'
                ]
            ]
        ];
        
        $addedItemResult = $this->repoService->register($blockCode, $fields, $manifest);
        $blockId = $addedItemResult->getId();
        $this->createdBlockCodes[] = $blockCode;
        
        self::assertGreaterThan(0, $blockId);
        
        // Verify the block was created with complex data
        $repoGetListResult = $this->repoService->getList(
            ['ID', 'NAME', 'MANIFEST'], 
            ['ID' => $blockId]
        );
        $blocks = $repoGetListResult->getRepoItems();
        
        self::assertCount(1, $blocks);
        
        $block = $blocks[0];
        self::assertEquals($fields['NAME'], $block->NAME);
        self::assertIsArray($block->MANIFEST);
    }
}