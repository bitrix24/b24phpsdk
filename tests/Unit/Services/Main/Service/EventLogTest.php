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
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Main\Service\EventLog;
use Bitrix24\SDK\Services\Main\Service\EventLogFilter;
use Bitrix24\SDK\Services\Main\Service\EventLogSelectBuilder;
use Bitrix24\SDK\Services\Main\Service\EventLogTailCursor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(EventLog::class)]
class EventLogTest extends TestCase
{
    #[DataProvider('argumentCases')]
    public function testGetMapsSelect(bool $builderSelect, bool $builderFilter): void
    {
        $service = $this->service('main.eventlog.get', ['id' => 7, 'select' => ['id', 'severity']], ['item' => ['id' => '7', 'severity' => 'INFO']]);
        $select = $builderSelect ? (new EventLogSelectBuilder())->severity() : ['id', 'severity'];
        $item = $service->get(7, $select)->eventLogItem();
        self::assertSame(7, $item->id);
        self::assertSame('INFO', $item->severity);
    }

    public function testGetDefaultSelect(): void
    {
        self::assertSame(7, $this->service('main.eventlog.get', ['id' => 7, 'select' => []], ['item' => ['id' => 7]])->get(7)->eventLogItem()->id);
    }

    #[DataProvider('invalidIds')]
    public function testGetRejectsInvalidId(int $id): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::never())->method('call');
        $this->expectException(InvalidArgumentException::class);
        (new EventLog($core, new NullLogger()))->get($id);
    }

    public static function invalidIds(): iterable
    {
        yield [0];
        yield [-1];
    }

    #[DataProvider('argumentCases')]
    public function testListMapsArguments(bool $builderSelect, bool $builderFilter): void
    {
        $select = $builderSelect ? (new EventLogSelectBuilder())->severity() : ['id', 'severity'];
        $filter = $builderFilter ? (new EventLogFilter())->severity()->eq('INFO') : [['severity', '=', 'INFO']];
        $service = $this->service('main.eventlog.list', [
            'select' => ['id', 'severity'], 'filter' => [['severity', '=', 'INFO']],
            'order' => ['id' => 'DESC', 'timestampX' => 'ASC'],
            'pagination' => ['page' => 2, 'limit' => 5, 'offset' => 0],
        ], ['items' => [['id' => 7], ['id' => 6]]]);
        $items = $service->list($select, $filter, ['id' => SortOrder::Descending, 'timestampX' => 'ASC'], ['page' => 2, 'limit' => 5, 'offset' => 0])->getEventLogItems();
        self::assertSame([7, 6], array_map(static fn ($item): int => $item->id, $items));
    }

    public function testListOmitsEmptyArgumentsAndDecodesEmptyItems(): void
    {
        self::assertSame([], $this->service('main.eventlog.list', [], ['items' => []])->list()->getEventLogItems());
    }

    #[DataProvider('argumentCases')]
    public function testTailMapsArguments(bool $builderSelect, bool $builderFilter): void
    {
        $select = $builderSelect ? (new EventLogSelectBuilder())->severity() : ['id', 'severity'];
        $filter = $builderFilter ? (new EventLogFilter())->severity()->eq('INFO') : [['severity', '=', 'INFO']];
        $service = $this->service('main.eventlog.tail', [
            'select' => ['id', 'severity'], 'filter' => [['severity', '=', 'INFO']],
            'cursor' => ['field' => 'id', 'order' => 'DESC', 'value' => 8, 'limit' => 5],
        ], ['items' => [['id' => 7]]]);
        self::assertSame(7, $service->tail($select, $filter, new EventLogTailCursor(8, order: SortOrder::Descending, limit: 5))->getEventLogItems()[0]->id);
    }

    public function testTailPreservesEmptyFilterAndZeroCursor(): void
    {
        $service = $this->service('main.eventlog.tail', [
            'select' => [], 'filter' => [], 'cursor' => ['field' => 'id', 'order' => 'ASC', 'value' => 0, 'limit' => 50],
        ], ['items' => []]);
        self::assertSame([], $service->tail([], new EventLogFilter(), new EventLogTailCursor(0))->getEventLogItems());
    }

    #[DataProvider('methods')]
    public function testPropagatesTransportErrors(string $method, array $arguments): void
    {
        $core = $this->createMock(CoreInterface::class);
        $transportException = new TransportException('connection failed');
        $core->expects(self::once())->method('call')->willThrowException($transportException);
        $this->expectExceptionObject($transportException);
        (new EventLog($core, new NullLogger()))->$method(...$arguments);
    }

    public static function methods(): iterable
    {
        yield ['get', [7]];
        yield ['list', []];
        yield ['tail', [[], [], new EventLogTailCursor(0)]];
    }

    public static function argumentCases(): iterable
    {
        yield 'arrays' => [false, false];
        yield 'select builder' => [true, false];
        yield 'filter builder' => [false, true];
        yield 'both builders' => [true, true];
    }

    private function service(string $method, array $parameters, array $result): EventLog
    {
        $response = new Response(
            (new MockHttpClient(new JsonMockResponse(['result' => $result])))->request('POST', 'https://example.test'),
            new Command($method, $parameters), new ApiLevelErrorHandler(new NullLogger()), new NullLogger()
        );
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($method, $parameters, ApiVersion::v3)->willReturn($response);
        return new EventLog($core, new NullLogger());
    }
}
