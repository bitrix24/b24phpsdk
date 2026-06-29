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
use Bitrix24\SDK\Services\Main\Service\Event;
use Bitrix24\SDK\Services\Main\Service\EventType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(Event::class)]
class EventTest extends TestCase
{
    #[Test]
    #[TestDox('bind() sends a well-formed event_type key (no tab) defaulting to online')]
    public function testBindSendsWellFormedEventTypeKeyDefaultingToOnline(): void
    {
        $captured = [];
        $event = new Event($this->makeCoreCapturing('event.bind', $captured), new NullLogger());

        $event->bind('ONCRMDEALADD', 'https://example.com/handler');

        $this->assertArrayHasKey('event_type', $captured);
        $this->assertArrayNotHasKey("event_type\t", $captured);
        $this->assertSame('online', $captured['event_type']);
    }

    #[Test]
    #[TestDox('bind() honours offline event_type and auth_connector')]
    public function testBindHonoursOfflineEventTypeAndAuthConnector(): void
    {
        $captured = [];
        $event = new Event($this->makeCoreCapturing('event.bind', $captured), new NullLogger());

        $event->bind(
            'ONCRMDEALADD',
            'https://example.com/handler',
            eventType: EventType::offline,
            authConnector: 'my_sync'
        );

        $this->assertSame('offline', $captured['event_type']);
        $this->assertSame('my_sync', $captured['auth_connector']);
    }

    #[Test]
    #[TestDox('bind() does not send auth_connector when it is not provided')]
    public function testBindDoesNotSendAuthConnectorWhenNotProvided(): void
    {
        $captured = [];
        $event = new Event($this->makeCoreCapturing('event.bind', $captured), new NullLogger());

        $event->bind('ONCRMDEALADD', 'https://example.com/handler');

        $this->assertArrayNotHasKey('auth_connector', $captured);
    }

    #[Test]
    #[TestDox('unbind() sends a well-formed event_type key (no tab)')]
    public function testUnbindSendsWellFormedEventTypeKey(): void
    {
        $captured = [];
        $event = new Event($this->makeCoreCapturing('event.unbind', $captured), new NullLogger());

        $event->unbind('ONCRMDEALADD', 'https://example.com/handler', eventType: EventType::offline);

        $this->assertArrayHasKey('event_type', $captured);
        $this->assertArrayNotHasKey("event_type\t", $captured);
        $this->assertSame('offline', $captured['event_type']);
    }

    /**
     * @param array<string, mixed> $captured captured by reference
     */
    private function makeCoreCapturing(string $expectedMethod, array &$captured): CoreInterface
    {
        $response = new Response(
            new MockResponse(''),
            new Command($expectedMethod, []),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );

        $core = $this->createStub(CoreInterface::class);
        $core->method('call')->willReturnCallback(
            function (string $apiMethod, array $parameters = []) use ($expectedMethod, &$captured, $response): Response {
                if ($apiMethod === $expectedMethod) {
                    $captured = $parameters;
                }

                return $response;
            }
        );

        return $core;
    }
}
