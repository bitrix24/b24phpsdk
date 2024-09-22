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

use Bitrix24\SDK\Core\Exceptions\PaymentRequiredException;
use Bitrix24\SDK\Core\Exceptions\WrongSecuritySignatureException;
use Bitrix24\SDK\Core\Requests\Events\UnsupportedRemoteEvent;
use Bitrix24\SDK\Services\RemoteEventsFabric;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(RemoteEventsFabric::class)]
class RemoteEventsFabricTest extends TestCase
{
    private RemoteEventsFabric $eventsFabric;

    #[Test]
    #[DataProvider('remoteEventsDataProvider')]
    public function testCreateEvent(
        Request $request,
        string  $applicationToken,
        string  $eventClassName,
        ?string $expectedException,
    ): void
    {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        $event = $this->eventsFabric->createEvent($request, $applicationToken);
        $this->assertEquals(
            $eventClassName,
            $event::class
        );

    }

    public static function remoteEventsDataProvider(): Generator
    {
        $applicationToken = '3c6c9248ec54af6bea1159b43ee0ab32';
        $rawRequest = 'event=ONCRMCONTACTADD&event_handler_id=58&data%5BFIELDS%5D%5BID%5D=134806&ts=1727018926&auth%5Baccess_token%5D=be45f0660071849a0058f18a000000010000077261ef50fc26f1357adbd86a052c9e48&auth%5Bexpires%5D=1727022526&auth%5Bexpires_in%5D=3600&auth%5Bscope%5D=crm%2Cplacement%2Cuser_brief&auth%5Bdomain%5D=bitrix24-php-sdk-playground.bitrix24.com&auth%5Bserver_endpoint%5D=https%3A%2F%2Foauth.bitrix.info%2Frest%2F&auth%5Bstatus%5D=L&auth%5Bclient_endpoint%5D=https%3A%2F%2Fbitrix24-php-sdk-playground.bitrix24.com%2Frest%2F&auth%5Bmember_id%5D=010b6886ebc205e43ae65000ee00addb&auth%5Buser_id%5D=1&auth%5Bapplication_token%5D=' . $applicationToken;
        parse_str('', $query);
        parse_str($rawRequest, $requestContent);
        $request = new Request(
            [],                    // GET parameters
            $requestContent,           // POST parameters
            [],                        // Additional attributes, if any
            [],                        // Cookies, if any
            [],                        // Files, if any
            [],                        // Server parameters, if any
            $rawRequest                // Raw content
        );
        $request->setMethod('POST');

        yield 'unsupported event valid signature' => [
            $request,
            $applicationToken,
            UnsupportedRemoteEvent::class,
            null
        ];
        yield 'unsupported event invalid signature' => [
            $request,
            $applicationToken.'-NEW',
            UnsupportedRemoteEvent::class,
            WrongSecuritySignatureException::class
        ];
    }

    protected function setUp(): void
    {
        $this->eventsFabric = RemoteEventsFabric::init(new NullLogger());
    }
}