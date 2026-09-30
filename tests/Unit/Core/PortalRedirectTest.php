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

use Bitrix24\SDK\Core\Core;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\CoreBuilder;
use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Bitrix24\SDK\Core\Credentials\AuthToken;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\Endpoints;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\PortalDomainChangeRejectedException;
use Bitrix24\SDK\Events\PortalDomainUrlChangedEvent;
use Bitrix24\SDK\Events\PortalDomainUrlChangingEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(Core::class)]
final class PortalRedirectTest extends TestCase
{
    public function testDenialPreventsRequestAndCredentialChange(): void
    {
        $credentials = $this->credentials();
        $eventDispatcher = new EventDispatcher();
        $events = [];
        $requests = [];
        $eventDispatcher->addListener(PortalDomainUrlChangingEvent::class, static function (PortalDomainUrlChangingEvent $event) use ($credentials, &$events): void {
            self::assertSame('https://old.example', $credentials->getDomainUrl());
            self::assertSame('https://old.example', $event->getOldDomainUrl());
            self::assertSame('https://new.example', $event->getNewDomainUrl());
            $events[] = 'changing';
            $event->deny('Reinstallation required');
        }, 10);
        $eventDispatcher->addListener(PortalDomainUrlChangingEvent::class, static function (PortalDomainUrlChangingEvent $event): void {
            self::assertTrue($event->isDenied());
            $event->deny('Another reason');
            self::assertSame('Reinstallation required', $event->getDenialReason());
        });
        $eventDispatcher->addListener(PortalDomainUrlChangedEvent::class, static function () use (&$events): void {
            $events[] = 'changed';
        });
        $core = $this->core($credentials, $eventDispatcher, $requests);
        try {
            $core->call('app.info');
            self::fail('A denied redirect must throw');
        } catch (PortalDomainChangeRejectedException $exception) {
            self::assertSame('https://old.example', $exception->getOldDomainUrl());
            self::assertSame('https://new.example', $exception->getNewDomainUrl());
            self::assertSame('Reinstallation required', $exception->getDenialReason());
            self::assertStringNotContainsString('fake-access-token', $exception->getMessage());
        }

        self::assertSame('https://old.example', $credentials->getDomainUrl());
        self::assertCount(1, $requests);
        self::assertSame(['changing'], $events);
    }

    public function testAllowedRedirectKeepsEventOrderAndToken(): void
    {
        $credentials = $this->credentials();
        $eventDispatcher = new EventDispatcher();
        $requests = [];
        $events = [];
        $eventDispatcher->addListener(PortalDomainUrlChangingEvent::class, static function (PortalDomainUrlChangingEvent $event) use ($credentials, &$requests, &$events): void {
            self::assertFalse($event->isDenied());
            self::assertNull($event->getDenialReason());
            self::assertSame('https://old.example', $credentials->getDomainUrl());
            self::assertCount(1, $requests);
            $events[] = 'changing';
            // Stopping listener propagation alone must not veto the redirect.
            $event->stopPropagation();
        });
        $eventDispatcher->addListener(PortalDomainUrlChangedEvent::class, static function (PortalDomainUrlChangedEvent $event) use ($credentials, &$requests, &$events): void {
            self::assertSame('old.example', $event->getOldDomainUrlHost());
            self::assertSame('new.example', $event->getNewDomainUrlHost());
            self::assertSame('https://new.example', $credentials->getDomainUrl());
            self::assertCount(2, $requests);
            $events[] = 'changed';
        });
        $this->core($credentials, $eventDispatcher, $requests)->call('app.info');
        self::assertSame(['changing', 'changed'], $events);
        self::assertSame('new.example', parse_url($requests[1]['url'], PHP_URL_HOST));
        self::assertStringContainsString('fake-access-token', $requests[1]['body']);
    }

    public function testRedirectWithoutListenersRemainsCompatible(): void
    {
        $requests = [];
        $credentials = $this->credentials();
        $this->core($credentials, new EventDispatcher(), $requests)->call('app.info');
        self::assertCount(2, $requests);
        self::assertSame('https://new.example', $credentials->getDomainUrl());
    }

    public function testEveryRedirectCanBeDeniedBeforeItsRequest(): void
    {
        $credentials = $this->credentials();
        $eventDispatcher = new EventDispatcher();
        $transitions = [];
        $requests = [];
        $changed = [];
        $eventDispatcher->addListener(PortalDomainUrlChangingEvent::class, static function (PortalDomainUrlChangingEvent $event) use (&$transitions): void {
            $transitions[] = [$event->getOldDomainUrl(), $event->getNewDomainUrl()];
            if ($event->getNewDomainUrl() === 'https://untrusted.example') {
                $event->deny('');
                $event->stopPropagation();
                self::assertTrue($event->isDenied());
                self::assertSame('', $event->getDenialReason());
            }
        });
        $eventDispatcher->addListener(PortalDomainUrlChangedEvent::class, static function () use (&$changed): void {
            $changed[] = true;
        });
        $mockHttpClient = new MockHttpClient(static function (string $method, string $url) use (&$requests): MockResponse {
            $requests[] = parse_url($url, PHP_URL_HOST);
            $target = count($requests) === 1 ? 'new.example' : 'untrusted.example';

            return new MockResponse('', ['http_code' => 302, 'response_headers' => ['location: https://' . $target . '/rest/app.info']]);
        });
        $core = (new CoreBuilder())->withCredentials($credentials)->withHttpClient($mockHttpClient)->withEventDispatcher($eventDispatcher)->build();
        try {
            $core->call('app.info');
            self::fail('Second redirect must be denied');
        } catch (PortalDomainChangeRejectedException $exception) {
            self::assertSame('https://new.example', $exception->getOldDomainUrl());
            self::assertSame('https://untrusted.example', $exception->getNewDomainUrl());
        }

        self::assertSame(['old.example', 'new.example'], $requests);
        self::assertSame([
            ['https://old.example', 'https://new.example'],
            ['https://new.example', 'https://untrusted.example'],
        ], $transitions);
        self::assertSame('https://new.example', $credentials->getDomainUrl());
        self::assertSame([], $changed);
    }

    private function credentials(): Credentials
    {
        return Credentials::createFromOAuth(
            new AuthToken('fake-access-token', 'fake-refresh-token', time() + 3600),
            new ApplicationProfile('client-id', 'client-secret', new Scope(['crm'])),
            new Endpoints('https://old.example', 'https://oauth.bitrix.info')
        );
    }

    /** @param list<array{url: string, body: string}> $requests */
    private function core(Credentials $credentials, EventDispatcher $dispatcher, array &$requests): CoreInterface
    {
        $mockHttpClient = new MockHttpClient(static function (string $method, string $url, array $options) use (&$requests): MockResponse {
            $requests[] = ['url' => $url, 'body' => $options['body']];
            if (count($requests) === 1) {
                return new MockResponse('', ['http_code' => 302, 'response_headers' => ['location: https://new.example/rest/app.info']]);
            }

            return new MockResponse('{"result":{}}', ['http_code' => 200]);
        });

        return (new CoreBuilder())->withCredentials($credentials)->withHttpClient($mockHttpClient)->withEventDispatcher($dispatcher)->build();
    }
}
