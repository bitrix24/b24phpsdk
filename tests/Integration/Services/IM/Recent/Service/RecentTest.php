<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Recent\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Recent\Service\Recent;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Recent::class)]
class RecentTest extends TestCase
{
    private Recent $recentService;

    #[\Override]
    protected function setUp(): void
    {
        $this->recentService = Fabric::getServiceBuilder()->getIMScope()->recent();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.recent.get returns a list of RecentItemResult')]
    public function testGet(): void
    {
        $items = $this->recentService->get()->items();

        $this->assertIsArray($items);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.recent.list returns a paginated list of RecentItemResult')]
    public function testList(): void
    {
        $items = $this->recentService->list(limit: 10)->items();

        $this->assertIsArray($items);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.recent.pin pins and unpins the first dialog in the recent list')]
    public function testPin(): void
    {
        $items = $this->recentService->get()->items();
        if ($items === []) {
            $this->markTestSkipped('No recent dialogs available for pin test');
        }

        $dialogId = $items[0]->id;

        $updatedItemResult = $this->recentService->pin($dialogId, true);
        $this->assertTrue($updatedItemResult->isSuccess());

        $unpinResult = $this->recentService->pin($dialogId, false);
        $this->assertTrue($unpinResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.recent.unread marks the first dialog as unread and then read')]
    public function testUnread(): void
    {
        $items = $this->recentService->get()->items();
        if ($items === []) {
            $this->markTestSkipped('No recent dialogs available for unread test');
        }

        $dialogId = $items[0]->id;

        $updatedItemResult = $this->recentService->unread($dialogId, 'mark');
        $this->assertTrue($updatedItemResult->isSuccess());

        $unmarkResult = $this->recentService->unread($dialogId, 'unmark');
        $this->assertTrue($unmarkResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.recent.hide removes the first dialog from the recent list')]
    public function testHide(): void
    {
        $items = $this->recentService->get()->items();
        if ($items === []) {
            $this->markTestSkipped('No recent dialogs available for hide test');
        }

        $dialogId = $items[0]->id;

        $updatedItemResult = $this->recentService->hide($dialogId);
        $this->assertTrue($updatedItemResult->isSuccess());
    }
}
