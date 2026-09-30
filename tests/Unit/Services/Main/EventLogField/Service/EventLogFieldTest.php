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

namespace Bitrix24\SDK\Tests\Unit\Services\Main\EventLogField\Service;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Main\EventLogField\Service\EventLogField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(EventLogField::class)]
class EventLogFieldTest extends TestCase
{
    #[DataProvider('selections')]
    public function testGetMapsArguments(array $select): void
    {
        $params = ['name' => 'timestampX'];
        if ($select !== []) {
            $params['select'] = $select;
        }

        $service = $this->service('main.eventlog.field.get', $params, ['item' => ['name' => 'timestampX', 'type' => 'datetime']]);
        self::assertSame('timestampX', $service->get('timestampX', $select)->eventLogField()->name);
    }

    #[DataProvider('selections')]
    public function testListMapsArguments(array $select): void
    {
        $service = $this->service('main.eventlog.field.list', $select === [] ? [] : ['select' => $select], ['items' => [['name' => 'id', 'type' => 'int']]]);
        self::assertSame('id', $service->list($select)->getEventLogFields()[0]->name);
    }

    public function testListDecodesEmptyItems(): void
    {
        self::assertSame([], $this->service('main.eventlog.field.list', [], ['items' => []])->list()->getEventLogFields());
    }

    public function testGetRejectsEmptyNameWithoutCallingCore(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::never())->method('call');
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type (intentionally exercise the runtime guard) */
        (new EventLogField($core, new NullLogger()))->get('');
    }

    public static function selections(): iterable
    {
        yield 'default' => [[]];
        yield 'selected' => [['name', 'type']];
    }

    private function service(string $method, array $parameters, array $result): EventLogField
    {
        $response = new Response(
            (new MockHttpClient(new JsonMockResponse(['result' => $result])))->request('POST', 'https://example.test'),
            new Command($method, $parameters), new ApiLevelErrorHandler(new NullLogger()), new NullLogger()
        );
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($method, $parameters, ApiVersion::v3)->willReturn($response);
        return new EventLogField($core, new NullLogger());
    }
}
