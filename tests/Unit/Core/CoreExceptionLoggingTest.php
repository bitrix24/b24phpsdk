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
use Bitrix24\SDK\Core\Contracts\ApiClientInterface;
use Bitrix24\SDK\Core\Core;
use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Bitrix24\SDK\Core\Credentials\AuthToken;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\Endpoints;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\EndpointUrlFormatter;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Infrastructure\HttpClient\RequestId\DefaultRequestIdGenerator;
use Generator;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Component\HttpClient\Exception\TransportException as HttpTransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(Core::class)]
class CoreExceptionLoggingTest extends TestCase
{
    /** @param class-string<\Exception> $failureClass */
    #[DataProvider('failureProvider')]
    public function testExpiredTokenRefreshFailureDoesNotExposeCredentials(string $failureClass, string $event, string $wrapperClass): void
    {
        $testHandler = new TestHandler(Level::Error);
        $logger = new Logger('regression', [$testHandler]);
        $requestCount = 0;
        $sourceFailure = null;
        $mockHttpClient = new MockHttpClient(static function (string $method, string $url) use ($failureClass, &$sourceFailure, &$requestCount): MockResponse {
            ++$requestCount;
            if ($requestCount === 1) {
                return new MockResponse('{"error":"expired_token"}', ['http_code' => 401]);
            }

            self::assertSame('GET', $method);
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            self::assertSame('FAKE_SECRET_552&= value', $query['client_secret']);
            self::assertSame('FAKE_REFRESH_552', $query['refresh_token']);
            $sourceFailure = new $failureClass(
                'Connection failed for "' . $url . '&access_token=FAKE_ACCESS_552"',
                42,
                new \RuntimeException('Previous request https://example.test/?auth=FAKE_PREVIOUS_552')
            );
            throw $sourceFailure;
        });
        $credentials = Credentials::createFromOAuth(
            new AuthToken('FAKE_ACCESS_552', 'FAKE_REFRESH_552', time() - 3600),
            new ApplicationProfile('public-client', 'FAKE_SECRET_552&= value', new Scope([])),
            new Endpoints('https://portal.example.test', 'https://oauth.example.test')
        );
        $defaultRequestIdGenerator = new DefaultRequestIdGenerator();
        $apiLevelErrorHandler = new ApiLevelErrorHandler($logger);
        $apiClient = new ApiClient($credentials, $mockHttpClient, $defaultRequestIdGenerator, $apiLevelErrorHandler, new EndpointUrlFormatter($defaultRequestIdGenerator, $logger), $logger);
        $core = new Core($apiClient, $apiLevelErrorHandler, new EventDispatcher(), $logger);

        try {
            $core->call('app.info');
            self::fail('The refresh failure must be thrown');
        } catch (BaseException $exception) {
            self::assertSame(2, $requestCount);
            self::assertInstanceOf(\Throwable::class, $sourceFailure);
            if (!filter_var(ini_get('zend.exception_ignore_args'), FILTER_VALIDATE_BOOLEAN)) {
                self::assertStringContainsString('FAKE_SECRET_552', json_encode(array_column($sourceFailure->getTrace(), 'args'), JSON_THROW_ON_ERROR));
            }

            self::assertSame($wrapperClass, $exception::class);
            self::assertSame(42, $exception->getCode());
            $records = $testHandler->getRecords();
            self::assertCount(1, $records);
            self::assertSame($event, $records[0]->message);
            foreach (['FAKE_SECRET_552', 'FAKE_REFRESH_552', 'FAKE_ACCESS_552', 'FAKE_PREVIOUS_552'] as $secret) {
                self::assertStringNotContainsString($secret, $records[0]->context['message']);
                self::assertStringNotContainsString($secret, $exception->getMessage());
                self::assertStringNotContainsString($secret, (string) $exception);
                self::assertStringNotContainsString($secret, json_encode($records[0]->context, JSON_THROW_ON_ERROR));
            }

            self::assertNull($exception->getPrevious());
            self::assertSame($failureClass, $records[0]->context['class']);
            self::assertStringContainsString('https://oauth.example.test/oauth/token/', $exception->getMessage());
            self::assertStringContainsString('grant_type=refresh_token', $exception->getMessage());
            self::assertStringContainsString('client_id=public-client', $exception->getMessage());
            self::assertNotEmpty($records[0]->context['trace']);
            foreach ($records[0]->context['trace'] as $frame) {
                self::assertSame([], array_diff(array_keys($frame), ['file', 'line', 'class', 'function', 'type']));
            }
        }
    }

    public static function failureProvider(): Generator
    {
        yield 'transport' => [HttpTransportException::class, 'call.transportException', TransportException::class];
        yield 'JSON' => [JsonException::class, 'call.transportException', TransportException::class];
        yield 'unknown' => [\RuntimeException::class, 'call.unknownException', BaseException::class];
    }

    public function testKnownSdkExceptionIsRethrownUnchanged(): void
    {
        $baseException = new BaseException('Known failure', 42, new \RuntimeException('Known cause'));
        $apiClient = $this->createStub(ApiClientInterface::class);
        $apiClient->method('getResponse')->willThrowException($baseException);
        $nullLogger = new NullLogger();
        $core = new Core($apiClient, new ApiLevelErrorHandler($nullLogger), new EventDispatcher(), $nullLogger);
        try {
            $core->call('app.info');
            self::fail('Expected known SDK exception');
        } catch (BaseException $exception) {
            self::assertSame($baseException, $exception);
        }
    }

    public function testNonSensitiveFailureRetainsDiagnostics(): void
    {
        $apiClient = $this->createStub(ApiClientInterface::class);
        $apiClient->method('getResponse')->willThrowException(new \RuntimeException('Connection refused', 42));
        $testHandler = new TestHandler(Level::Error);
        $logger = new Logger('regression', [$testHandler]);
        $core = new Core($apiClient, new ApiLevelErrorHandler($logger), new EventDispatcher(), $logger);
        try {
            $core->call('app.info');
            self::fail('Expected failure');
        } catch (BaseException $exception) {
            self::assertSame('unknown error - Connection refused', $exception->getMessage());
            self::assertSame(42, $exception->getCode());
            self::assertSame('Connection refused', $testHandler->getRecords()[0]->context['message']);
        }
    }
}
