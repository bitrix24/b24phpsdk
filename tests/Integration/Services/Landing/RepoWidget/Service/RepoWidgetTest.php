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
        foreach ($this->createdWidgetCodes as $createdWidgetCode) {
            try {
                $this->repoWidgetService->unregister($createdWidgetCode);
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

        $addedItemResult = $this->repoWidgetService->register($code, $fields);
        $this->createdWidgetCodes[] = $code;

        self::assertGreaterThan(0, $addedItemResult->getId());
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
        $addedItemResult = $this->repoWidgetService->register($code, $fields1);
        $this->createdWidgetCodes[] = $code;

        $fields2 = [
            'NAME'    => 'Updated Widget ' . time(),
            'ACTIVE'  => 'Y',
            'CONTENT' => '<div>Updated content</div>',
        ];
        $result2 = $this->repoWidgetService->register($code, $fields2);

        self::assertGreaterThan(0, $addedItemResult->getId());
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

        $deletedItemResult = $this->repoWidgetService->unregister($code);

        self::assertTrue($deletedItemResult->isSuccess());
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
        $addedItemResult = $this->repoWidgetService->register($code, $fields);
        $this->createdWidgetCodes[] = $code;
        $widgetId = $addedItemResult->getId();

        $repoWidgetGetListResult = $this->repoWidgetService->getList();
        $widgets = $repoWidgetGetListResult->getRepoWidgetItems();

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
        $addedItemResult = $this->repoWidgetService->register($code, $fields);
        $this->createdWidgetCodes[] = $code;
        $widgetId = $addedItemResult->getId();

        $repoWidgetGetListResult = $this->repoWidgetService->getList(
            ['ID', 'NAME', 'ACTIVE'],
            ['ID' => $widgetId]
        );
        $widgets = $repoWidgetGetListResult->getRepoWidgetItems();

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
        $repoWidgetDebugResult = $this->repoWidgetService->debug(true);

        self::assertTrue($repoWidgetDebugResult->isEnabled());

        // Restore debug mode to disabled
        $this->repoWidgetService->debug(false);
    }
}
