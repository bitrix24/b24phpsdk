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

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Contracts\ApiClientInterface;
use Bitrix24\SDK\Core\Core;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Core\Exceptions\PortalUnavailableException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(Core::class)]
class CoreTest extends TestCase
{
    #[Test]
    #[TestDox('call() throws PortalUnavailableException when 302 redirect stays on the same domain')]
    public function testCallThrowsPortalUnavailableExceptionOnSameDomainRedirect(): void
    {
        $domainUrl = 'https://myportal.example.com';
        $redirectLocation = $domainUrl . '/bitrix/coupon_activation.php';

        $mockResponse = $this->createStub(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(302);
        $mockResponse->method('getHeaders')->willReturn(['location' => [$redirectLocation]]);

        $credentials = Credentials::createFromWebhook(new WebhookUrl($domainUrl . '/rest/1/token/'));
        $mockApiClient = $this->createStub(ApiClientInterface::class);
        $mockApiClient->method('getCredentials')->willReturn($credentials);
        $mockApiClient->method('getResponse')->willReturn($mockResponse);

        $core = new Core(
            $mockApiClient,
            new ApiLevelErrorHandler(new NullLogger()),
            new EventDispatcher(),
            new NullLogger()
        );

        $this->expectException(PortalUnavailableException::class);
        $this->expectExceptionMessageMatches('/portal redirect loop detected/');
        $core->call('app.info');
    }

    #[Test]
    #[TestDox('call() does NOT throw PortalUnavailableException when 302 redirect is to a different domain')]
    public function testCallDoesNotThrowOnDifferentDomainRedirect(): void
    {
        $oldDomain = 'https://old-portal.example.com';
        $newDomain = 'https://new-portal.example.com';
        $redirectLocation = $newDomain . '/rest/app.info';

        // First response: 302 redirect to new domain
        $redirectResponse = $this->createStub(ResponseInterface::class);
        $redirectResponse->method('getStatusCode')->willReturn(302);
        $redirectResponse->method('getHeaders')->willReturn(['location' => [$redirectLocation]]);

        // Second response: 200 OK after domain change
        $okResponse = $this->createStub(ResponseInterface::class);
        $okResponse->method('getStatusCode')->willReturn(200);
        $okResponse->method('toArray')->willReturn([
            'result' => [],
            'time' => ['start' => 0.0, 'finish' => 0.0, 'duration' => 0.0, 'processing' => 0.0, 'date_start' => '', 'date_finish' => ''],
        ]);

        $credentials = Credentials::createFromWebhook(new WebhookUrl($oldDomain . '/rest/1/token/'));
        $mockApiClient = $this->createStub(ApiClientInterface::class);
        $mockApiClient->method('getCredentials')->willReturn($credentials);
        $mockApiClient->method('getResponse')->willReturnOnConsecutiveCalls($redirectResponse, $okResponse);

        $core = new Core(
            $mockApiClient,
            new ApiLevelErrorHandler(new NullLogger()),
            new EventDispatcher(),
            new NullLogger()
        );

        // Should NOT throw PortalUnavailableException — domain change is a valid scenario
        $this->expectNotToPerformAssertions();

        try {
            $core->call('app.info');
        } catch (PortalUnavailableException) {
            $this->fail('PortalUnavailableException must NOT be thrown for a real domain change redirect');
        } catch (\Throwable) {
            // Other exceptions (e.g. from Response parsing) are acceptable in this unit test context
        }
    }
}
