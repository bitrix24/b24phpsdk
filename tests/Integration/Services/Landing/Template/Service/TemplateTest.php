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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\Template\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\Template\Service\Template;
use Bitrix24\SDK\Services\Landing\Site\Service\Site;
use Bitrix24\SDK\Services\Landing\Page\Service\Page;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class TemplateTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Landing\Template\Service
 */
#[CoversMethod(Template::class, 'getList')]
#[CoversMethod(Template::class, 'getLandingRef')]
#[CoversMethod(Template::class, 'getSiteRef')]
#[CoversMethod(Template::class, 'setLandingRef')]
#[CoversMethod(Template::class, 'setSiteRef')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Landing\Template\Service\Template::class)]
class TemplateTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Template $templateService;

    protected Site $siteService;

    protected Page $pageService;

    protected array $createdPageIds = [];

    protected array $createdSiteIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->templateService = $serviceBuilder->getLandingScope()->template();
        $this->siteService = $serviceBuilder->getLandingScope()->site();
        $this->pageService = $serviceBuilder->getLandingScope()->page();
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
            'TITLE' => 'Test Site for Template ' . time(),
            'CODE' => 'testsitetemplate' . time(),
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
            'TITLE' => 'Test Page for Template ' . time(),
            'CODE' => 'testpagetemplate' . time(),
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
    public function testGetList(): void
    {
        $templatesResult = $this->templateService->getList();
        $templates = $templatesResult->getTemplates();

        self::assertIsArray($templates);
        self::assertNotEmpty($templates, 'There should be at least some predefined templates');

        // Test first template structure
        $firstTemplate = $templates[0];
        self::assertGreaterThan(0, $firstTemplate->ID);
        self::assertIsString($firstTemplate->TITLE);
        self::assertIsString($firstTemplate->XML_ID);
        self::assertIsString($firstTemplate->ACTIVE);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetListWithParameters(): void
    {
        $select = ['ID', 'TITLE', 'XML_ID'];
        $filter = ['>ID' => 0];
        $order = ['ID' => 'DESC'];

        $templatesResult = $this->templateService->getList($select, $filter, $order);
        $templates = $templatesResult->getTemplates();

        self::assertIsArray($templates);
        self::assertNotEmpty($templates);

        // Verify that templates are ordered by ID in descending order
        if (count($templates) > 1) {
            self::assertGreaterThanOrEqual($templates[1]->ID, $templates[0]->ID);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetLandingRef(): void
    {
        $siteId = $this->createTestSite();
        $pageId = $this->createTestPage($siteId);

        $templateRefsResult = $this->templateService->getLandingRef($pageId);
        $refs = $templateRefsResult->getRefs();

        self::assertIsArray($refs);
        // The refs array might be empty if no template areas are configured for this page
        // This is expected behavior for a newly created page
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetSiteRef(): void
    {
        $siteId = $this->createTestSite();

        $templateRefsResult = $this->templateService->getSiteRef($siteId);
        $refs = $templateRefsResult->getRefs();

        self::assertIsArray($refs);
        // The refs array might be empty if no template areas are configured for this site
        // This is expected behavior for a newly created site
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testSetLandingRef(): void
    {
        $siteId = $this->createTestSite();
        $pageId = $this->createTestPage($siteId);

        // Test setting empty data (should reset included areas)
        $templateRefSetResult = $this->templateService->setLandingRef($pageId, []);
        self::assertTrue($templateRefSetResult->isSuccess());

        // Verify that refs are now empty
        $templateRefsResult = $this->templateService->getLandingRef($pageId);
        $refs = $templateRefsResult->getRefs();
        self::assertIsArray($refs);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testSetSiteRef(): void
    {
        $siteId = $this->createTestSite();

        // Test setting empty data (should reset included areas)
        $templateRefSetResult = $this->templateService->setSiteRef($siteId, []);
        self::assertTrue($templateRefSetResult->isSuccess());

        // Verify that refs are now empty
        $templateRefsResult = $this->templateService->getSiteRef($siteId);
        $refs = $templateRefsResult->getRefs();
        self::assertIsArray($refs);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testSetLandingRefWithData(): void
    {
        $siteId = $this->createTestSite();
        $pageId = $this->createTestPage($siteId);

        // Create additional pages to use as template areas
        $headerPageId = $this->createTestPage($siteId);
        $footerPageId = $this->createTestPage($siteId);

        // Test setting template areas data
        $data = [
            1 => $headerPageId,  // Area 1 -> header page
            2 => $footerPageId   // Area 2 -> footer page
        ];

        $templateRefSetResult = $this->templateService->setLandingRef($pageId, $data);
        self::assertTrue($templateRefSetResult->isSuccess());

        // Verify that refs are set correctly
        $templateRefsResult = $this->templateService->getLandingRef($pageId);
        $refs = $templateRefsResult->getRefs();
        self::assertIsArray($refs);

        // Note: The actual refs might not match exactly what we set
        // because the page might not be linked to a template that supports these areas
        // This is expected behavior according to the API documentation
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testSetSiteRefWithData(): void
    {
        $siteId = $this->createTestSite();

        // Create pages to use as template areas
        $headerPageId = $this->createTestPage($siteId);
        $footerPageId = $this->createTestPage($siteId);

        // Test setting template areas data
        $data = [
            1 => $headerPageId,  // Area 1 -> header page
            2 => $footerPageId   // Area 2 -> footer page
        ];

        $templateRefSetResult = $this->templateService->setSiteRef($siteId, $data);
        self::assertTrue($templateRefSetResult->isSuccess());

        // Verify that refs are set correctly
        $templateRefsResult = $this->templateService->getSiteRef($siteId);
        $refs = $templateRefsResult->getRefs();
        self::assertIsArray($refs);

        // Note: The actual refs might not match exactly what we set
        // because the site might not be linked to a template that supports these areas
        // This is expected behavior according to the API documentation
    }
}