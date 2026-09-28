<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Main\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\Main\Common\EventHandlerMetadata;
use Bitrix24\SDK\Services\Main\Service\Event;
use Bitrix24\SDK\Services\Main\Service\EventManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(EventManager::class)]
#[CoversClass(EventHandlerMetadata::class)]
#[CoversClass(Event::class)]
class EventManagerTest extends TestCase
{
    #[DataProvider('metadataOptionsCases')]
    public function testMetadataOptionsReachEffectiveEventValidation(string $handler, array $options, array $expectedPayload): void
    {
        $eventHandlerMetadata = new EventHandlerMetadata('ONCRMDEALADD', $handler, 7, $options);
        $responseData = $this->createStub(ResponseData::class);
        $responseData->method('getResult')->willReturn([]);
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn($responseData);
        $calls = [];
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->exactly(2))->method('call')->willReturnCallback(
            static function (string $method, array $parameters = []) use (&$calls, $response): Response {
                $calls[] = [$method, $parameters];
                return $response;
            }
        );
        $nullLogger = new NullLogger();
        (new EventManager(new Event($core, $nullLogger), $nullLogger))->bindEventHandlers([$eventHandlerMetadata]);
        self::assertSame([['event.get', []], ['event.bind', $expectedPayload]], $calls);
        self::assertSame($handler, $eventHandlerMetadata->handlerUrl);
    }

    public static function metadataOptionsCases(): iterable
    {
        yield 'offline empty metadata handler' => [
            '',
            ['event_type' => 'offline'],
            ['event' => 'ONCRMDEALADD', 'handler' => '', 'event_type' => 'offline', 'auth_type' => 7],
        ];
        foreach (['https://example.com/handler', new Url('https://example.com/handler')] as $index => $handler) {
            yield 'options override placeholder '.$index => [
                'placeholder',
                ['handler' => $handler],
                ['event' => 'ONCRMDEALADD', 'handler' => 'https://example.com/handler', 'event_type' => 'online', 'auth_type' => 7],
            ];
        }
    }
}
