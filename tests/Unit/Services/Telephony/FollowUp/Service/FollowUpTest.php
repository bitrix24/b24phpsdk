<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Telephony\FollowUp\Service;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Telephony\FollowUp\Result\FollowUpResult;
use Bitrix24\SDK\Services\Telephony\FollowUp\Result\FollowUpsResult;
use Bitrix24\SDK\Services\Telephony\FollowUp\Service\FollowUp;
use Bitrix24\SDK\Services\Telephony\FollowUp\Service\FollowUpListCursor;
use Bitrix24\SDK\Services\Telephony\FollowUp\Service\FollowUpListPagination;
use Bitrix24\SDK\Services\Telephony\FollowUp\Service\FollowUpMentionFormat;
use Bitrix24\SDK\Services\Telephony\FollowUp\Service\FollowUpSelectBuilder;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(FollowUp::class)]
class FollowUpTest extends TestCase
{
    private FollowUp $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new FollowUp(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testListReturnsFollowUpsResult(): void
    {
        $this->assertInstanceOf(FollowUpsResult::class, $this->service->list(['startDate' => ['from' => '2026-01-01T00:00:00Z', 'to' => '2026-01-31T23:59:59Z']]));
    }

    #[Test]
    public function testGetReturnsFollowUpResult(): void
    {
        $this->assertInstanceOf(FollowUpResult::class, $this->service->get(12345));
    }

    #[Test]
    #[TestDox('list() forwards a raw filter array unchanged')]
    public function testListForwardsRawFilter(): void
    {
        $filter = ['startDate' => ['from' => '2026-01-01T00:00:00Z', 'to' => '2026-01-31T23:59:59Z']];

        [$method, $captured] = $this->call(static fn (FollowUp $service) => $service->list($filter));

        $this->assertSame('call.followup.list', $method);
        $this->assertSame($filter, $captured['filter']);
        $this->assertSame(['startDate' => 'desc'], $captured['order']);
        $this->assertArrayNotHasKey('pagination', $captured);
        $this->assertArrayNotHasKey('mentionFormat', $captured);
    }

    #[Test]
    #[TestDox('list() builds select from a FollowUpSelectBuilder')]
    public function testListBuildsSelectFromBuilder(): void
    {
        $select = (new FollowUpSelectBuilder())->callId()->startDate();

        [$method, $captured] = $this->call(static fn (FollowUp $service) => $service->list([], $select));

        $this->assertSame('call.followup.list', $method);
        $this->assertSame($select->buildSelect(), $captured['select']);
    }

    #[Test]
    #[TestDox('list() forwards limit and afterCursor from a typed FollowUpListPagination')]
    public function testListForwardsTypedPagination(): void
    {
        $followUpListPagination = new FollowUpListPagination(20, new FollowUpListCursor('2026-01-12T14:30:00.000000+00:00', 12330));

        [$method, $captured] = $this->call(static fn (FollowUp $service) => $service->list([], [], ['startDate' => 'desc'], $followUpListPagination));

        $this->assertSame('call.followup.list', $method);
        $this->assertSame(
            ['limit' => 20, 'afterCursor' => ['startDate' => '2026-01-12T14:30:00.000000+00:00', 'id' => 12330]],
            $captured['pagination']
        );
    }

    #[Test]
    #[TestDox('list() forwards mentionFormat as its scalar value')]
    public function testListForwardsMentionFormat(): void
    {
        [$method, $captured] = $this->call(
            static fn (FollowUp $service) => $service->list([], [], ['startDate' => 'desc'], null, FollowUpMentionFormat::Html)
        );

        $this->assertSame('call.followup.list', $method);
        $this->assertSame('html', $captured['mentionFormat']);
    }

    #[Test]
    #[TestDox('get() forwards callId and a plain select array unchanged')]
    public function testGetForwardsPlainSelect(): void
    {
        [$method, $captured] = $this->call(static fn (FollowUp $service) => $service->get(12345, ['callId', 'startDate']));

        $this->assertSame('call.followup.get', $method);
        $this->assertSame(12345, $captured['callId']);
        $this->assertSame(['callId', 'startDate'], $captured['select']);
        $this->assertArrayNotHasKey('mentionFormat', $captured);
    }

    #[Test]
    #[TestDox('get() builds select from a FollowUpSelectBuilder')]
    public function testGetBuildsSelectFromBuilder(): void
    {
        $select = (new FollowUpSelectBuilder())->participants();

        [$method, $captured] = $this->call(static fn (FollowUp $service) => $service->get(12345, $select));

        $this->assertSame('call.followup.get', $method);
        $this->assertSame($select->buildSelect(), $captured['select']);
    }

    #[Test]
    #[TestDox('get() forwards mentionFormat as its scalar value')]
    public function testGetForwardsMentionFormat(): void
    {
        [$method, $captured] = $this->call(
            static fn (FollowUp $service) => $service->get(12345, [], FollowUpMentionFormat::None)
        );

        $this->assertSame('call.followup.get', $method);
        $this->assertSame('none', $captured['mentionFormat']);
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function call(callable $action): array
    {
        $method = null;
        $captured = [];
        $response = new Response(
            new MockResponse(''),
            new Command('', []),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );

        $core = $this->createStub(CoreInterface::class);
        $core->method('call')->willReturnCallback(
            function (string $apiMethod, array $parameters = []) use (&$method, &$captured, $response): Response {
                $method = $apiMethod;
                $captured = $parameters;

                return $response;
            }
        );

        $action(new FollowUp($core, new NullLogger()));

        return [$method, $captured];
    }
}
