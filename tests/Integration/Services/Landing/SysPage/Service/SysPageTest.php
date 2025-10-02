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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\SysPage\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\SysPage\Service\SysPage;
use Bitrix24\SDK\Services\Landing\SysPage\SysPageType;
use Bitrix24\SDK\Services\Landing\Site\Service\Site;
use Bitrix24\SDK\Services\Landing\Page\Service\Page;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class SysPageTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Landing\SysPage\Service
 */
#[CoversMethod(SysPage::class, 'set')]
#[CoversMethod(SysPage::class, 'get')]
#[CoversMethod(SysPage::class, 'getSpecialPage')]
#[CoversMethod(SysPage::class, 'deleteForLanding')]
#[CoversMethod(SysPage::class, 'deleteForSite')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Landing\SysPage\Service\SysPage::class)]
class SysPageTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected SysPage $sysPageService;

    protected Site $siteService;

    protected Page $pageService;

    protected array $createdPageIds = [];

    protected array $createdSiteIds = [];

    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->sysPageService = $serviceBuilder->getLandingScope()->sysPage();
        $this->siteService = $serviceBuilder->getLandingScope()->site();
        $this->pageService = $serviceBuilder->getLandingScope()->page();
    }

    protected function tearDown(): void
    {
        // Clean up system page settings before deleting pages and sites
        foreach ($this->createdSiteIds as $siteId) {
            try {
                $this->sysPageService->deleteForSite($siteId);
            } catch (\Exception) {
                // Ignore if site or system pages don't exist
            }
        }

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
            'TITLE' => 'Test Site for SysPage ' . time(),
            'CODE' => 'testsitesyspage' . time(),
            'TYPE' => 'PAGE'
        ];

        $addedItemResult = $this->siteService->add($siteFields);
        $siteId = $addedItemResult->getId();
        $this->createdSiteIds[] = $siteId;

        return $siteId;
    }

    /**
     * Helper method to create a test page
     */
    protected function createTestPage(int $siteId): int
    {
        $pageFields = [
            'TITLE' => 'Test Page for SysPage ' . time(),
            'CODE' => 'testpagesyspage' . time(),
            'SITE_ID' => $siteId,
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
    public function testSetWithEnum(): void
    {
        $siteId = $this->createTestSite();
        $pageId = $this->createTestPage($siteId);

        $sysPageResult = $this->sysPageService->set($siteId, SysPageType::personal, $pageId);

        self::assertTrue($sysPageResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testSetWithString(): void
    {
        $siteId = $this->createTestSite();
        $pageId = $this->createTestPage($siteId);

        $sysPageResult = $this->sysPageService->set($siteId, 'cart', $pageId);

        self::assertTrue($sysPageResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testSetWithoutPageId(): void
    {
        $siteId = $this->createTestSite();

        // First set a system page
        $pageId = $this->createTestPage($siteId);
        $this->sysPageService->set($siteId, SysPageType::catalog, $pageId);

        // Then remove it by calling set without pageId
        $sysPageResult = $this->sysPageService->set($siteId, SysPageType::catalog);

        self::assertTrue($sysPageResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $siteId = $this->createTestSite();
        $pageId = $this->createTestPage($siteId);

        // Set a system page first
        $this->sysPageService->set($siteId, SysPageType::personal, $pageId);

        $sysPageListResult = $this->sysPageService->get($siteId);
        $sysPages = $sysPageListResult->getSysPages();

        self::assertIsArray($sysPages);
        // At least one system page should be set
        self::assertGreaterThanOrEqual(1, count($sysPages));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetWithActiveFilter(): void
    {
        $siteId = $this->createTestSite();
        $pageId = $this->createTestPage($siteId);

        // Set a system page first
        $this->sysPageService->set($siteId, SysPageType::personal, $pageId);

        $sysPageListResult = $this->sysPageService->get($siteId, true);
        $sysPages = $sysPageListResult->getSysPages();

        self::assertIsArray($sysPages);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetSpecialPageWithEnum(): void
    {
        $siteId = $this->createTestSite();
        $pageId = $this->createTestPage($siteId);

        // Set a system page first
        $this->sysPageService->set($siteId, SysPageType::personal, $pageId);

        $sysPageUrlResult = $this->sysPageService->getSpecialPage($siteId, SysPageType::personal);
        $url = $sysPageUrlResult->getUrl();

        self::assertIsString($url);
        self::assertNotEmpty($url);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetSpecialPageWithString(): void
    {
        $siteId = $this->createTestSite();
        $pageId = $this->createTestPage($siteId);

        // Set a system page first
        $this->sysPageService->set($siteId, 'cart', $pageId);

        $sysPageUrlResult = $this->sysPageService->getSpecialPage($siteId, 'cart');
        $url = $sysPageUrlResult->getUrl();

        self::assertIsString($url);
        self::assertNotEmpty($url);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetSpecialPageWithAdditionalParams(): void
    {
        $siteId = $this->createTestSite();
        $pageId = $this->createTestPage($siteId);

        // Set a system page first
        $this->sysPageService->set($siteId, SysPageType::personal, $pageId);

        $additional = ['SECTION' => 'private'];
        $sysPageUrlResult = $this->sysPageService->getSpecialPage($siteId, SysPageType::personal, $additional);
        $url = $sysPageUrlResult->getUrl();

        self::assertIsString($url);
        self::assertNotEmpty($url);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDeleteForLanding(): void
    {
        $siteId = $this->createTestSite();
        $pageId = $this->createTestPage($siteId);

        // Set a system page first
        $this->sysPageService->set($siteId, SysPageType::personal, $pageId);

        $sysPageResult = $this->sysPageService->deleteForLanding($pageId);

        self::assertTrue($sysPageResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDeleteForSite(): void
    {
        $siteId = $this->createTestSite();
        $pageId = $this->createTestPage($siteId);

        // Set multiple system pages
        $this->sysPageService->set($siteId, SysPageType::personal, $pageId);
        $this->sysPageService->set($siteId, SysPageType::cart, $pageId);

        $sysPageResult = $this->sysPageService->deleteForSite($siteId);

        self::assertTrue($sysPageResult->isSuccess());
    }

    /**
     * Test that all enum values are valid
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testAllSysPageTypes(): void
    {
        $siteId = $this->createTestSite();
        $pageId = $this->createTestPage($siteId);

        $types = [
            SysPageType::mainpage,
            SysPageType::catalog,
            SysPageType::personal,
            SysPageType::cart,
            SysPageType::order,
            SysPageType::payment,
            SysPageType::compare,
        ];

        foreach ($types as $type) {
            $result = $this->sysPageService->set($siteId, $type, $pageId);
            self::assertTrue($result->isSuccess(), 'Failed to set system page type: ' . $type->value);
        }

        // Clean up - remove all system pages
        $this->sysPageService->deleteForSite($siteId);
    }
}
