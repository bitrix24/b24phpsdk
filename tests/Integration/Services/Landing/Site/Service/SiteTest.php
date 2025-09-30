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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\Site\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\Site\Result\SiteItemResult;
use Bitrix24\SDK\Services\Landing\Site\Service\Site;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class SiteTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Landing\Site\Service
 */
#[CoversMethod(Site::class, 'add')]
#[CoversMethod(Site::class, 'getList')]
#[CoversMethod(Site::class, 'update')]
#[CoversMethod(Site::class, 'delete')]
#[CoversMethod(Site::class, 'getPublicUrl')]
#[CoversMethod(Site::class, 'getPreview')]
#[CoversMethod(Site::class, 'publication')]
#[CoversMethod(Site::class, 'unpublic')]
#[CoversMethod(Site::class, 'markDelete')]
#[CoversMethod(Site::class, 'markUnDelete')]
#[CoversMethod(Site::class, 'getFolders')]
#[CoversMethod(Site::class, 'addFolder')]
#[CoversMethod(Site::class, 'updateFolder')]
#[CoversMethod(Site::class, 'publicationFolder')]
#[CoversMethod(Site::class, 'unPublicFolder')]
#[CoversMethod(Site::class, 'markFolderDelete')]
#[CoversMethod(Site::class, 'markFolderUnDelete')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Landing\Site\Service\Site::class)]
class SiteTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Site $siteService;

    protected array $createdSiteIds = [];

    protected array $createdFolderIds = [];

    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->siteService = $serviceBuilder->getLandingScope()->site();
    }

    protected function tearDown(): void
    {
        // Clean up created folders
        foreach ($this->createdFolderIds as $createdFolderId) {
            try {
                $this->siteService->markFolderDelete($createdFolderId);
            } catch (\Exception) {
                // Ignore if folder doesn't exist
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
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $siteFields = [
            'TITLE' => 'Test Site ' . time(),
            'CODE' => 'test-site-' . time(),
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        self::assertGreaterThan(0, $siteId);
        self::assertIsInt($siteId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetList(): void
    {
        // First create a test site
        $timestamp = time();
        $siteFields = [
            'TITLE' => 'Test Site for List ' . $timestamp,
            'CODE' => 'test-site-list-' . $timestamp,
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        // Test getList with no parameters
        $sitesResult = $this->siteService->getList();
        $sites = $sitesResult->getSites();

        self::assertIsArray($sites);
        self::assertNotEmpty($sites);

        // Check that our created site is in the list
        $foundSite = null;
        foreach ($sites as $site) {
            self::assertInstanceOf(SiteItemResult::class, $site);
            if (intval($site->ID) === $siteId) {
                $foundSite = $site;
                break;
            }
        }

        self::assertNotNull($foundSite, 'Created site should be found in the list');
        self::assertEquals($siteFields['TITLE'], $foundSite->TITLE);
        self::assertStringContainsString($siteFields['CODE'], $foundSite->CODE);
        self::assertEquals($siteFields['TYPE'], $foundSite->TYPE);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetListWithFilters(): void
    {
        // First create a test site with unique title
        $timestamp = time();
        $uniqueTitle = 'Unique Test Site ' . $timestamp;
        $siteFields = [
            'TITLE' => $uniqueTitle,
            'CODE' => 'unique-test-site-' . $timestamp,
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        // Test getList with filters
        $sitesResult = $this->siteService->getList(
            ['ID', 'TITLE', 'CODE'], // select
            ['TITLE' => $uniqueTitle], // filter
            ['ID' => 'DESC'] // order
        );
        $sites = $sitesResult->getSites();

        self::assertIsArray($sites);
        self::assertCount(1, $sites, 'Should find exactly one site with this unique title');

        $foundSite = $sites[0];
        self::assertInstanceOf(SiteItemResult::class, $foundSite);
        self::assertEquals($siteId, intval($foundSite->ID));
        self::assertEquals($uniqueTitle, $foundSite->TITLE);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a test site
        $timestamp = time();
        $siteFields = [
            'TITLE' => 'Test Site for Update ' . $timestamp,
            'CODE' => 'test-site-update-' . $timestamp,
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        // Update the site
        $newTitle = 'Updated Test Site ' . $timestamp;
        $updatedItemResult = $this->siteService->update($siteId, [
            'TITLE' => $newTitle
        ]);

        self::assertTrue($updatedItemResult->isSuccess(), 'Site update should be successful');

        // Verify the update by getting the site list
        $sitesResult = $this->siteService->getList(
            ['ID', 'TITLE'],
            ['ID' => $siteId]
        );
        $sites = $sitesResult->getSites();

        self::assertNotEmpty($sites);
        $updatedSite = $sites[0];
        self::assertEquals($newTitle, $updatedSite->TITLE);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a test site
        $timestamp = time();
        $siteFields = [
            'TITLE' => 'Test Site for Delete ' . $timestamp,
            'CODE' => 'test-site-delete-' . $timestamp,
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();

        // Delete the site
        $deletedItemResult = $this->siteService->delete($siteId);
        self::assertTrue($deletedItemResult->isSuccess(), 'Site deletion should be successful');

        // Remove from cleanup list since it's already deleted
        $this->createdSiteIds = array_filter($this->createdSiteIds, fn($id): bool => $id !== $siteId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetPublicUrl(): void
    {
        // Create a test site
        $timestamp = time();
        $siteFields = [
            'TITLE' => 'Test Site for URL ' . $timestamp,
            'CODE' => 'test-site-url-' . $timestamp,
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        // Get public URL
        $siteUrlResult = $this->siteService->getPublicUrl($siteId);
        $url = $siteUrlResult->getUrl();

        self::assertIsString($url);
        // URL might be empty if site is not published, but method should work
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetPreview(): void
    {
        // Create a test site
        $timestamp = time();
        $siteFields = [
            'TITLE' => 'Test Site for Preview ' . $timestamp,
            'CODE' => 'test-site-preview-' . $timestamp,
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        // Get preview URL
        $siteUrlResult = $this->siteService->getPreview($siteId);
        $previewUrl = $siteUrlResult->getUrl();

        self::assertIsString($previewUrl);
        // Preview URL might be empty, but method should work
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testPublicationAndUnpublic(): void
    {
        // Create a test site
        $timestamp = time();
        $siteFields = [
            'TITLE' => 'Test Site for Publication ' . $timestamp,
            'CODE' => 'test-site-publication-' . $timestamp,
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        // Test publication
        $sitePublishedResult = $this->siteService->publication($siteId);
        self::assertTrue($sitePublishedResult->isSuccess(), 'Site publication should be successful');

        // Test unpublish
        $siteUnpublishedResult = $this->siteService->unpublic($siteId);
        self::assertTrue($siteUnpublishedResult->isSuccess(), 'Site unpublish should be successful');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testMarkDeleteAndMarkUnDelete(): void
    {
        // Create a test site
        $timestamp = time();
        $siteFields = [
            'TITLE' => 'Test Site for Mark Delete ' . $timestamp,
            'CODE' => 'test-site-mark-delete-' . $timestamp,
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        // Test mark delete
        $siteMarkedDeletedResult = $this->siteService->markDelete($siteId);
        self::assertTrue($siteMarkedDeletedResult->isSuccess(), 'Site mark delete should be successful');

        // Test mark undelete
        $siteMarkedUnDeletedResult = $this->siteService->markUnDelete($siteId);
        self::assertTrue($siteMarkedUnDeletedResult->isSuccess(), 'Site mark undelete should be successful');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFolders(): void
    {
        // Create a test site
        $timestamp = time();
        $siteFields = [
            'TITLE' => 'Test Site for Folders ' . $timestamp,
            'CODE' => 'test-site-folders-' . $timestamp,
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        // Get folders (might be empty initially)
        $foldersResult = $this->siteService->getFolders($siteId);
        $folders = $foldersResult->getFolders();

        self::assertIsArray($folders);
        // Folders array might be empty for a new site, that's OK
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAddFolder(): void
    {
        // Create a test site
        $timestamp = time();
        $siteFields = [
            'TITLE' => 'Test Site for Add Folder ' . $timestamp,
            'CODE' => 'test-site-add-folder-' . $timestamp,
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        // Add a folder
        $folderFields = [
            'TITLE' => 'Test Folder ' . $timestamp,
            'CODE' => 'test-folder-' . $timestamp,
            'ACTIVE' => 'Y'
        ];

        $folderAddedResult = $this->siteService->addFolder($siteId, $folderFields);
        $folderId = $folderAddedResult->getId();
        $this->createdFolderIds[] = $folderId;

        self::assertGreaterThan(0, $folderId);
        self::assertIsInt($folderId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdateFolder(): void
    {
        // Create a test site
        $timestamp = time();
        $siteFields = [
            'TITLE' => 'Test Site for Update Folder ' . $timestamp,
            'CODE' => 'test-site-update-folder-' . $timestamp,
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        // Add a folder
        $folderFields = [
            'TITLE' => 'Test Folder for Update ' . $timestamp,
            'CODE' => 'test-folder-update-' . $timestamp,
            'ACTIVE' => 'Y'
        ];

        $folderAddedResult = $this->siteService->addFolder($siteId, $folderFields);
        $folderId = $folderAddedResult->getId();
        $this->createdFolderIds[] = $folderId;

        // Update the folder
        $folderUpdatedResult = $this->siteService->updateFolder($siteId, $folderId, [
            'TITLE' => 'Updated Test Folder ' . $timestamp
        ]);

        self::assertTrue($folderUpdatedResult->isSuccess(), 'Folder update should be successful');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFolderPublicationMethods(): void
    {
        // Create a test site
        $timestamp = time();
        $siteFields = [
            'TITLE' => 'Test Site for Folder Publication ' . $timestamp,
            'CODE' => 'test-site-folder-pub-' . $timestamp,
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        // Add a folder
        $folderFields = [
            'TITLE' => 'Test Folder for Publication ' . $timestamp,
            'CODE' => 'test-folder-pub-' . $timestamp,
            'ACTIVE' => 'Y'
        ];

        $folderAddedResult = $this->siteService->addFolder($siteId, $folderFields);
        $folderId = $folderAddedResult->getId();
        $this->createdFolderIds[] = $folderId;

        // Test folder publication
        $folderPublishedResult = $this->siteService->publicationFolder($folderId);
        self::assertTrue($folderPublishedResult->isSuccess(), 'Folder publication should be successful');

        // Test folder unpublish
        $folderUnpublishedResult = $this->siteService->unPublicFolder($folderId);
        self::assertTrue($folderUnpublishedResult->isSuccess(), 'Folder unpublish should be successful');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFolderMarkDeleteAndUnDelete(): void
    {
        // Create a test site
        $timestamp = time();
        $siteFields = [
            'TITLE' => 'Test Site for Folder Delete ' . $timestamp,
            'CODE' => 'test-site-folder-delete-' . $timestamp,
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        // Add a folder
        $folderFields = [
            'TITLE' => 'Test Folder for Delete ' . $timestamp,
            'CODE' => 'test-folder-delete-' . $timestamp,
            'ACTIVE' => 'Y'
        ];

        $folderAddedResult = $this->siteService->addFolder($siteId, $folderFields);
        $folderId = $folderAddedResult->getId();
        $this->createdFolderIds[] = $folderId;

        // Test folder mark delete
        $deletedItemResult = $this->siteService->markFolderDelete($folderId);
        self::assertTrue($deletedItemResult->isSuccess(), 'Folder mark delete should be successful');

        // Test folder mark undelete
        $markUnDeleteResult = $this->siteService->markFolderUnDelete($folderId);
        self::assertTrue($markUnDeleteResult->isSuccess(), 'Folder mark undelete should be successful');
    }
}