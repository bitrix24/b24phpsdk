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
use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Bitrix24\SDK\Core\Credentials\AuthToken;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\Endpoints;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\InvalidGrantException;
use Bitrix24\SDK\Core\Exceptions\PortalDomainNotFoundException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Exceptions\WrongClientException;
use Bitrix24\SDK\Infrastructure\HttpClient\RequestId\DefaultRequestIdGenerator;
use Fig\Http\Message\StatusCodeInterface;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Throwable;

#[CoversClass(ApiClient::class)]
class ApiClientTest extends TestCase
{
    #[DataProvider('getNewAuthTokenErrorsDataProvider')]
    #[Test]
    #[TestDox('test getNewAuthToken error handling')]
    public function testGetNewAuthTokenErrorHandling(
        int $httpStatusCode,
        array $responseBody,
        ?Throwable $expectedException
    ): void {
        if ($expectedException instanceof Throwable) {
            $this->expectException($expectedException::class);
        }

        // Create mock HTTP client with predefined response
        $mockResponse = new MockResponse(
            json_encode($responseBody),
            ['http_code' => $httpStatusCode]
        );
        $mockHttpClient = new MockHttpClient($mockResponse);

        // Create credentials
        $credentials = Credentials::createFromOAuth(
            new AuthToken('test-access-token', 'test-refresh-token', 3600),
            new ApplicationProfile('test-client-id', 'test-client-secret', new Scope([])),
            new Endpoints('https://test.bitrix24.com', 'https://oauth.bitrix.info/')
        );

        // Create ApiClient instance
        $apiClient = new ApiClient(
            $credentials,
            $mockHttpClient,
            new DefaultRequestIdGenerator(),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );

        $apiClient->getNewAuthToken();
    }

    public static function getNewAuthTokenErrorsDataProvider(): Generator
    {
        yield 'invalid_grant error - expired refresh token' => [
            StatusCodeInterface::STATUS_BAD_REQUEST,
            [
                'error' => 'invalid_grant',
                'error_description' => 'The provided authorization grant is invalid, expired, revoked'
            ],
            new InvalidGrantException()
        ];

        yield 'bad_verification_code error' => [
            StatusCodeInterface::STATUS_BAD_REQUEST,
            [
                'error' => 'bad_verification_code',
                'error_description' => 'Bad verification code'
            ],
            new InvalidGrantException()
        ];

        yield 'invalid_client error - wrong client credentials' => [
            StatusCodeInterface::STATUS_UNAUTHORIZED,
            [
                'error' => 'invalid_client',
                'error_description' => 'Client authentication failed'
            ],
            new WrongClientException()
        ];

        yield 'portal not found - HTTP 404' => [
            StatusCodeInterface::STATUS_NOT_FOUND,
            [
                'error' => 'not_found',
                'error_description' => 'Portal not found'
            ],
            new PortalDomainNotFoundException()
        ];

        yield 'portal domain error in description' => [
            StatusCodeInterface::STATUS_BAD_REQUEST,
            [
                'error' => 'unknown_error',
                'error_description' => 'Portal domain not found or inaccessible'
            ],
            new PortalDomainNotFoundException()
        ];

        yield 'server error - HTTP 500' => [
            StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            [
                'error' => 'server_error',
                'error_description' => 'Internal server error'
            ],
            new TransportException()
        ];

        yield 'service unavailable - HTTP 503' => [
            StatusCodeInterface::STATUS_SERVICE_UNAVAILABLE,
            [
                'error' => 'service_unavailable',
                'error_description' => 'Service temporarily unavailable'
            ],
            new TransportException()
        ];

        yield 'bad gateway - HTTP 502' => [
            StatusCodeInterface::STATUS_BAD_GATEWAY,
            [
                'error' => 'bad_gateway',
                'error_description' => 'Bad gateway'
            ],
            new TransportException()
        ];

        yield 'gateway timeout - HTTP 504' => [
            StatusCodeInterface::STATUS_GATEWAY_TIMEOUT,
            [
                'error' => 'gateway_timeout',
                'error_description' => 'Gateway timeout'
            ],
            new TransportException()
        ];

        yield 'unknown error code in bad request' => [
            StatusCodeInterface::STATUS_BAD_REQUEST,
            [
                'error' => 'unknown_error',
                'error_description' => 'Some unknown error occurred'
            ],
            new TransportException()
        ];

        yield 'unauthorized with unknown error' => [
            StatusCodeInterface::STATUS_UNAUTHORIZED,
            [
                'error' => 'unknown_auth_error',
                'error_description' => 'Unknown authorization error'
            ],
            new TransportException()
        ];

        yield 'unknown HTTP status code' => [
            StatusCodeInterface::STATUS_FORBIDDEN,
            [
                'error' => 'forbidden',
                'error_description' => 'Access forbidden'
            ],
            new TransportException()
        ];
    }

    #[Test]
    #[TestDox('test getNewAuthToken success')]
    public function testGetNewAuthTokenSuccess(): void
    {
        $successResponse = [
            'access_token' => 'new-access-token',
            'refresh_token' => 'new-refresh-token',
            'expires_in' => 3600,
            'scope' => 'user',
            'domain' => 'test.bitrix24.com',
            'server_endpoint' => 'https://test.bitrix24.com/rest/',
            'status' => 'L',
            'client_endpoint' => 'https://test.bitrix24.com/rest/',
            'member_id' => 'test-member-id',
            'user_id' => 1,
        ];

        $mockResponse = new MockResponse(
            json_encode($successResponse),
            ['http_code' => StatusCodeInterface::STATUS_OK]
        );
        $mockHttpClient = new MockHttpClient($mockResponse);

        $credentials = Credentials::createFromOAuth(
            new AuthToken('test-access-token', 'test-refresh-token', 3600),
            new ApplicationProfile('test-client-id', 'test-client-secret', new Scope([])),
            new Endpoints('https://test.bitrix24.com', 'https://oauth.bitrix.info/')
        );

        $apiClient = new ApiClient(
            $credentials,
            $mockHttpClient,
            new DefaultRequestIdGenerator(),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );

        $renewedToken = $apiClient->getNewAuthToken();

        $this->assertEquals('new-access-token', $renewedToken->authToken->accessToken);
        $this->assertEquals('new-refresh-token', $renewedToken->authToken->refreshToken);
        $this->assertEquals(3600, $renewedToken->authToken->expires);
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}
