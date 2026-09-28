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

namespace Bitrix24\SDK\Tests\Unit\Services;
use Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Entity\Bitrix24AccountInterface;
use Bitrix24\SDK\Core\Credentials\{ApplicationProfile, AuthToken, Scope};
use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
#[CoversClass(ServiceBuilderFactory::class)]
class ServiceBuilderFactoryTest extends TestCase
{
    public function testWebhookFactoriesAcceptUrl(): void
    {
        $url = new Url('https://example.com/rest/1/test/');
        $serviceBuilderFactory = new ServiceBuilderFactory(new EventDispatcher(), new NullLogger());
        foreach ([$serviceBuilderFactory->initFromWebhook($url), ServiceBuilderFactory::createServiceBuilderFromWebhook($url)] as $builder) {
            self::assertSame($url->getUrl(), $builder->getMainScope()->main()->core->getApiClient()->getCredentials()->getWebhookUrl()->getUrl());
        }
    }
    public function testOAuthFactoryAcceptsUrlAndLegacyDomain(): void
    {
        $serviceBuilderFactory = new ServiceBuilderFactory(new EventDispatcher(), new NullLogger());
        foreach ([new Url('https://example.com'), 'example.com'] as $domain) {
            $builder = $serviceBuilderFactory->init(new ApplicationProfile('client','secret',new Scope(['crm'])), new AuthToken('access', 'refresh', 123), $domain, new Url('https://oauth.example.com'));
            $credentials = $builder->getMainScope()->main()->core->getApiClient()->getCredentials();
            self::assertSame('https://example.com', $credentials->getDomainUrl());
            self::assertSame('https://oauth.example.com', $credentials->getEndpoints()->getAuthServerUrl());
        }
    }
    public function testAccountFactoryAcceptsOAuthUrl(): void
    {
        $account = $this->createStub(Bitrix24AccountInterface::class);
        $account->method('getDomainUrl')->willReturn('example.com');
        $account->method('getAuthToken')->willReturn(new AuthToken('access', 'refresh', 123));
        $builder = (new ServiceBuilderFactory(new EventDispatcher(), new NullLogger()))->initFromAccount(new ApplicationProfile('client','secret',new Scope(['crm'])), $account, new Url('https://oauth.example.com'));
        self::assertSame('https://oauth.example.com', $builder->getMainScope()->main()->core->getApiClient()->getCredentials()->getEndpoints()->getAuthServerUrl());
    }
    public function testPlacementFactoryAcceptsOAuthUrl(): void
    {
        $request = new Request(['DOMAIN'=>'example.com'], ['AUTH_ID'=>'access','REFRESH_ID'=>'refresh','AUTH_EXPIRES'=>3600]);
        $builder = ServiceBuilderFactory::createServiceBuilderFromPlacementRequest($request, new ApplicationProfile('client','secret',new Scope(['crm'])), oauthServerUrl: new Url('https://oauth.example.com'));
        self::assertSame('https://oauth.example.com', $builder->getMainScope()->main()->core->getApiClient()->getCredentials()->getEndpoints()->getAuthServerUrl());
    }
}
