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

namespace Bitrix24\SDK\Tests\Unit\Services\Main\Service;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Main\Service\OfflineEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(OfflineEvent::class)]
class OfflineEventTest extends TestCase
{
    #[Test]
    #[TestDox('get() calls event.offline.get and clears the queue by default')]
    public function testGetClearsByDefault(): void
    {
        $method = null;
        $captured = [];
        $offlineEvent = new OfflineEvent($this->makeCoreCapturing($method, $captured), new NullLogger());

        $offlineEvent->get();

        $this->assertSame('event.offline.get', $method);
        $this->assertSame(1, $captured['clear']);
        $this->assertArrayNotHasKey('filter', $captured);
        $this->assertArrayNotHasKey('auth_connector', $captured);
    }

    #[Test]
    #[TestDox('get() forwards filter, auth_connector and clear=0 when requested')]
    public function testGetForwardsFilterAuthConnectorAndNoClear(): void
    {
        $method = null;
        $captured = [];
        $offlineEvent = new OfflineEvent($this->makeCoreCapturing($method, $captured), new NullLogger());

        $offlineEvent->get(['=EVENT_NAME' => 'ONCRMDEALADD'], 'my_sync', false);

        $this->assertSame('event.offline.get', $method);
        $this->assertSame(0, $captured['clear']);
        $this->assertSame(['=EVENT_NAME' => 'ONCRMDEALADD'], $captured['filter']);
        $this->assertSame('my_sync', $captured['auth_connector']);
    }

    #[Test]
    #[TestDox('list() calls event.offline.list with filter and order')]
    public function testListForwardsFilterAndOrder(): void
    {
        $method = null;
        $captured = [];
        $offlineEvent = new OfflineEvent($this->makeCoreCapturing($method, $captured), new NullLogger());

        $offlineEvent->list(['ERROR' => 0], ['ID' => 'DESC']);

        $this->assertSame('event.offline.list', $method);
        $this->assertSame(['ERROR' => 0], $captured['filter']);
        $this->assertSame(['ID' => 'DESC'], $captured['order']);
    }

    #[Test]
    #[TestDox('clear() calls event.offline.clear with process_id and id list')]
    public function testClearForwardsProcessIdAndIds(): void
    {
        $method = null;
        $captured = [];
        $offlineEvent = new OfflineEvent($this->makeCoreCapturing($method, $captured), new NullLogger());

        $offlineEvent->clear('proc-123', [2, 3]);

        $this->assertSame('event.offline.clear', $method);
        $this->assertSame('proc-123', $captured['process_id']);
        $this->assertSame([2, 3], $captured['id']);
    }

    #[Test]
    #[TestDox('error() calls event.offline.error with process_id and message_id list')]
    public function testErrorForwardsProcessIdAndMessageIds(): void
    {
        $method = null;
        $captured = [];
        $offlineEvent = new OfflineEvent($this->makeCoreCapturing($method, $captured), new NullLogger());

        $offlineEvent->error('proc-123', ['20b324c42fce9afb3fe27b05cc83a66e']);

        $this->assertSame('event.offline.error', $method);
        $this->assertSame('proc-123', $captured['process_id']);
        $this->assertSame(['20b324c42fce9afb3fe27b05cc83a66e'], $captured['message_id']);
    }

    /**
     * @param array<string, mixed> $captured captured by reference
     */
    private function makeCoreCapturing(?string &$method, array &$captured): CoreInterface
    {
        $response = new Response(
            new MockResponse(''),
            new Command('', []),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );

        $core = $this->createMock(CoreInterface::class);
        $core->method('call')->willReturnCallback(
            function (string $apiMethod, array $parameters = []) use (&$method, &$captured, $response): Response {
                $method = $apiMethod;
                $captured = $parameters;

                return $response;
            }
        );

        return $core;
    }
}
