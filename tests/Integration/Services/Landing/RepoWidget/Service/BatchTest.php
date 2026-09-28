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
use Bitrix24\SDK\Services\Landing\RepoWidget\Service\Batch;
use Bitrix24\SDK\Services\Landing\RepoWidget\Service\RepoWidget;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private RepoWidget $repoWidgetService;

    /** @var string[] */
    private array $createdWidgetCodes = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->repoWidgetService = Factory::getServiceBuilder(true)->getLandingScope()->repoWidget();
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
     * @return array<string, int> registered widget ids keyed by widget code
     * @throws BaseException
     */
    private function registerWidgets(int $count): array
    {
        $suffix = uniqid();
        $widgets = [];
        for ($i = 1; $i <= $count; $i++) {
            $widgets[] = [
                'code' => sprintf('sdk_batch_widget_%d_%s', $i, $suffix),
                'fields' => [
                    'NAME' => sprintf('SDK Batch Widget %d %s', $i, $suffix),
                    'ACTIVE' => 'Y',
                    'SECTIONS' => 'widgets_company_life',
                    'PREVIEW' => 'https://example.com/preview.png',
                    'CONTENT' => '<div class="w-container">{{desc}}</div>',
                    'WIDGET_PARAMS' => [
                        'rootNode' => '.w-container',
                        'handler' => 'https://example.com/widget-handler.php',
                        'demoData' => ['desc' => 'SDK batch test widget'],
                    ],
                ],
            ];
        }

        $registered = [];
        foreach ($this->repoWidgetService->batch->register($widgets) as $cnt => $addedItemBatchResult) {
            $registered[$widgets[$cnt]['code']] = $addedItemBatchResult->getId();
        }

        $this->createdWidgetCodes = array_merge($this->createdWidgetCodes, array_keys($registered));

        return $registered;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch register adds multiple widgets')]
    public function testBatchRegister(): void
    {
        $registered = $this->registerWidgets(3);

        self::assertCount(3, $registered);
        foreach ($registered as $id) {
            self::assertGreaterThan(0, $id);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch list returns registered widgets')]
    public function testBatchList(): void
    {
        $registered = $this->registerWidgets(3);
        $ids = array_values($registered);

        $foundIds = [];
        foreach ($this->repoWidgetService->batch->list(['ID', 'NAME'], ['ID' => $ids]) as $repoWidgetItemResult) {
            self::assertInstanceOf(RepoWidgetItemResult::class, $repoWidgetItemResult);
            $foundIds[] = (int)$repoWidgetItemResult->ID;
        }

        sort($foundIds);
        self::assertSame($ids, $foundIds);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch unregister removes multiple widgets')]
    public function testBatchUnregister(): void
    {
        $registered = $this->registerWidgets(3);
        $codes = array_keys($registered);

        $cnt = 0;
        foreach ($this->repoWidgetService->batch->unregister($codes) as $deletedItemBatchResult) {
            $cnt++;
            self::assertTrue($deletedItemBatchResult->isSuccess());
        }

        self::assertSame(count($codes), $cnt);
        self::assertSame([], $this->repoWidgetService->getList(['ID'], ['ID' => array_values($registered)])->getRepoWidgetItems());
        $this->createdWidgetCodes = [];
    }
}
