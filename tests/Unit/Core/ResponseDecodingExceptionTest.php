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

namespace Bitrix24\SDK\Tests\Unit\Core;

use Bitrix24\SDK\Core\ApiClient;
use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Core;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Core\EndpointUrlFormatter;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\ResponseDecodingException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Infrastructure\HttpClient\RequestId\DefaultRequestIdGenerator;
use Generator;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Component\HttpClient\Exception\TransportException as HttpTransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;

#[CoversClass(Core::class)]
#[CoversClass(ResponseDecodingException::class)]
class ResponseDecodingExceptionTest extends TestCase
{
    #[DataProvider('malformedResponseProvider')]
    public function testMalformedErrorResponseIsCatchableAsTransportException(int $status, string $body): void
    {
        $core = $this->makeCore(new MockHttpClient(new MockResponse($body, ['http_code' => $status])));

        try {
            $core->call('app.info');
            self::fail('Expected response decoding failure');
        } catch (TransportException $exception) {
            self::assertSame(ResponseDecodingException::class, $exception::class);
            self::assertNull($exception->getPrevious());
            self::assertStringContainsString(JsonException::class, $exception->getMessage());
        }
    }

    public static function malformedResponseProvider(): Generator
    {
        foreach ([400, 401, 403, 503, 500] as $status) {
            foreach (self::malformedBodyProvider() as $name => [$body]) {
                yield $status . ' ' . $name => [$status, $body];
            }
        }
    }

    #[DataProvider('malformedBodyProvider')]
    public function testHttp200ResponseKeepsLazyDecodingBehavior(string $body): void
    {
        $core = $this->makeCore(new MockHttpClient(new MockResponse($body, ['http_code' => 200])));
        $response = $core->call('app.info');
        self::assertInstanceOf(Response::class, $response);

        try {
            $response->getResponseData();
            self::fail('Expected lazy response decoding failure');
        } catch (BaseException $exception) {
            self::assertSame(BaseException::class, $exception::class);
            self::assertInstanceOf(JsonException::class, $exception->getPrevious());
        }
    }

    public static function malformedBodyProvider(): Generator
    {
        yield 'HTML' => ['<!DOCTYPE html><html><body>Proxy error</body></html>'];
        yield 'truncated JSON' => ['{"result":'];
    }

    public function testDecodingContractImplementationPreservesSafeDiagnostics(): void
    {
        $source = new class (
            'Invalid response from https://portal.example.test/?auth=FAKE_AUTH_667&client_secret=FAKE_SECRET_667&refresh_token=FAKE_REFRESH_667',
            42,
            new \RuntimeException('Previous request https://example.test/?auth=FAKE_PREVIOUS_667')
        ) extends \RuntimeException implements DecodingExceptionInterface {};
        $testHandler = new TestHandler(Level::Error);
        $logger = new Logger('decoding', [$testHandler]);
        $core = $this->makeCore(new MockHttpClient(static function () use ($source): never {
            throw $source;
        }), $logger);

        try {
            $core->call('app.info');
            self::fail('Expected response decoding failure');
        } catch (BaseException $exception) {
            self::assertSame(ResponseDecodingException::class, $exception::class);
            self::assertSame(42, $exception->getCode());
            self::assertNull($exception->getPrevious());
            $records = $testHandler->getRecords();
            self::assertCount(1, $records);
            self::assertSame('call.transportException', $records[0]->message);
            self::assertSame($source::class, $records[0]->context['class']);
            self::assertNotEmpty($records[0]->context['trace']);
            foreach (['FAKE_AUTH_667', 'FAKE_SECRET_667', 'FAKE_REFRESH_667', 'FAKE_PREVIOUS_667'] as $secret) {
                self::assertStringNotContainsString($secret, $exception->getMessage());
                self::assertStringNotContainsString($secret, (string) $exception);
                self::assertStringNotContainsString($secret, json_encode($records[0]->context, JSON_THROW_ON_ERROR));
            }

            foreach ($records[0]->context['trace'] as $frame) {
                self::assertSame([], array_diff(array_keys($frame), ['file', 'line', 'class', 'function', 'type']));
            }
        }
    }

    public function testNetworkFailureKeepsOrdinaryTransportException(): void
    {
        $core = $this->makeCore(new MockHttpClient(static function (): never {
            throw new HttpTransportException('Connection refused', 42);
        }));

        try {
            $core->call('app.info');
            self::fail('Expected network failure');
        } catch (TransportException $exception) {
            self::assertSame(TransportException::class, $exception::class);
            self::assertSame(42, $exception->getCode());
            self::assertNull($exception->getPrevious());
            self::assertStringContainsString('Connection refused', $exception->getMessage());
        }
    }

    private function makeCore(MockHttpClient $httpClient, ?LoggerInterface $logger = null): Core
    {
        $logger ??= new NullLogger();
        $defaultRequestIdGenerator = new DefaultRequestIdGenerator();
        $apiLevelErrorHandler = new ApiLevelErrorHandler($logger);
        $apiClient = new ApiClient(
            Credentials::createFromWebhook(new WebhookUrl('https://portal.example.test/rest/1/token/')),
            $httpClient,
            $defaultRequestIdGenerator,
            $apiLevelErrorHandler,
            new EndpointUrlFormatter($defaultRequestIdGenerator, $logger),
            $logger
        );

        return new Core($apiClient, $apiLevelErrorHandler, new EventDispatcher(), $logger);
    }
}
