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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\RepoWidget\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\RepoWidget\Result\RepoWidgetItemResult;
use Bitrix24\SDK\Services\Landing\RepoWidget\Service\RepoWidget;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RepoWidget::class)]
class RepoWidgetTest extends TestCase
{
    private RepoWidget $repoWidgetService;

    /** @var string[] */
    private array $createdWidgetCodes = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->repoWidgetService = Factory::getServiceBuilder()->getLandingScope()->repoWidget();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdWidgetCodes as $code) {
            try {
                $this->repoWidgetService->unregister($code);
            } catch (\Exception) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testRegister(): void
    {
        $code = 'test_widget_register_' . time();
        $fields = [
            'NAME'     => 'Test Vibe Widget ' . time(),
            'ACTIVE'   => 'Y',
            'SECTIONS' => 'widgets_company_life',
            'PREVIEW'  => 'https://example.com/preview.png',
            'CONTENT'  => '<div class="w-container">{{desc}}</div>',
        ];

        $result = $this->repoWidgetService->register($code, $fields);
        $this->createdWidgetCodes[] = $code;

        self::assertGreaterThan(0, $result->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testRegisterWithExistingCodeUpdatesWidget(): void
    {
        $code = 'test_widget_update_' . time();
        $fields1 = [
            'NAME'    => 'First Widget ' . time(),
            'ACTIVE'  => 'Y',
            'CONTENT' => '<div>First content</div>',
        ];
        $result1 = $this->repoWidgetService->register($code, $fields1);
        $this->createdWidgetCodes[] = $code;

        $fields2 = [
            'NAME'    => 'Updated Widget ' . time(),
            'ACTIVE'  => 'Y',
            'CONTENT' => '<div>Updated content</div>',
        ];
        $result2 = $this->repoWidgetService->register($code, $fields2);

        self::assertGreaterThan(0, $result1->getId());
        self::assertGreaterThan(0, $result2->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUnregister(): void
    {
        $code = 'test_widget_unregister_' . time();
        $fields = [
            'NAME'    => 'Widget to remove ' . time(),
            'ACTIVE'  => 'Y',
            'CONTENT' => '<div>Content</div>',
        ];
        $this->repoWidgetService->register($code, $fields);

        $result = $this->repoWidgetService->unregister($code);

        self::assertTrue($result->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetList(): void
    {
        $code = 'test_widget_list_' . time();
        $fields = [
            'NAME'    => 'List Test Widget ' . time(),
            'ACTIVE'  => 'Y',
            'CONTENT' => '<div>{{count}}</div>',
        ];
        $addResult = $this->repoWidgetService->register($code, $fields);
        $this->createdWidgetCodes[] = $code;
        $widgetId = $addResult->getId();

        $listResult = $this->repoWidgetService->getList();
        $widgets = $listResult->getRepoWidgetItems();

        self::assertIsArray($widgets);
        self::assertNotEmpty($widgets);

        $found = null;
        foreach ($widgets as $widget) {
            self::assertInstanceOf(RepoWidgetItemResult::class, $widget);
            if ((int)$widget->ID === $widgetId) {
                $found = $widget;
                break;
            }
        }

        self::assertNotNull($found, 'Registered widget must be present in getList response');
        self::assertEquals($fields['NAME'], $found->NAME);
        self::assertEquals('Y', $found->ACTIVE);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetListWithSelectAndFilter(): void
    {
        $code = 'test_widget_filter_' . time();
        $fields = [
            'NAME'    => 'Filter Test Widget ' . time(),
            'ACTIVE'  => 'Y',
            'CONTENT' => '<div>filter</div>',
        ];
        $addResult = $this->repoWidgetService->register($code, $fields);
        $this->createdWidgetCodes[] = $code;
        $widgetId = $addResult->getId();

        $listResult = $this->repoWidgetService->getList(
            ['ID', 'NAME', 'ACTIVE'],
            ['ID' => $widgetId]
        );
        $widgets = $listResult->getRepoWidgetItems();

        self::assertCount(1, $widgets);
        self::assertEquals($widgetId, (int)$widgets[0]->ID);
        self::assertEquals($fields['NAME'], $widgets[0]->NAME);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDebug(): void
    {
        $result = $this->repoWidgetService->debug(true);

        self::assertTrue($result->isEnabled());

        // Restore debug mode to disabled
        $this->repoWidgetService->debug(false);
    }
}
