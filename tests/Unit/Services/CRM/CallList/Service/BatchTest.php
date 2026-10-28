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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\CallList\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\CRM\CallList\Batch as CallListBatch;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListItemResult;
use Bitrix24\SDK\Services\CRM\CallList\Service\Batch;
use Bitrix24\SDK\Tests\Unit\Services\CRM\CallList\ResponseFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Batch::class)]
#[CoversClass(CallListBatch::class)]
class BatchTest extends TestCase
{
    use ResponseFixture;

    public static function operations(): array
    {
        return ['add' => ['add'], 'update' => ['update']];
    }

    #[DataProvider('operations')]
    public function testEmptyInputsDoNotCallApi(string $operation): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::never())->method('call');
        self::assertSame([], iterator_to_array($this->service($core)->{$operation}([])));
    }

    #[DataProvider('operations')]
    public function testTransportSplitsCommandsAndPreservesFlatPayload(string $operation): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->method('getAuthConnector')->willReturn('call-list-source');
        $observed = [];
        $packetSizes = [];
        $core->expects(self::exactly(3))->method('call')->willReturnCallback(
            function (string $method, array $parameters) use ($operation, &$observed, &$packetSizes): Response {
                self::assertSame('batch', $method);
                self::assertTrue($parameters['halt']);
                $packetSizes[] = count($parameters['cmd']);
                $results = [];
                $times = [];
                foreach ($parameters['cmd'] as $key => $command) {
                    [$submethod, $query] = explode('?', $command, 2);
                    self::assertSame('crm.calllist.' . $operation, $submethod);
                    parse_str($query, $payload);
                    self::assertArrayNotHasKey('fields', $payload);
                    self::assertArrayNotHasKey('id', $payload);
                    self::assertSame('CONTACT', $payload['ENTITY_TYPE']);
                    self::assertSame('call-list-source', $payload['auth_connector']);
                    $index = count($observed);
                    self::assertSame([(string)($index + 1)], $payload['ENTITIES']);
                    if ($index % 2 === 0) {
                        self::assertSame('0', $payload['WEBFORM_ID']);
                    } else {
                        self::assertArrayNotHasKey('WEBFORM_ID', $payload);
                    }

                    if ($operation === 'update') {
                        self::assertSame((string)(1000 + $index), $payload['LIST_ID']);
                    }

                    $observed[] = $payload;
                    $results[$key] = $operation === 'add' ? (string)(1000 + $index) : true;
                    $times[$key] = ['start' => 0, 'finish' => 0, 'duration' => 0, 'processing' => 0,
                        'date_start' => '2026-10-28T00:00:00+00:00', 'date_finish' => '2026-10-28T00:00:00+00:00'];
                }

                return $this->response(['result' => $results, 'result_time' => $times,
                    'result_error' => [], 'result_next' => [], 'result_total' => []], 'batch');
            }
        );
        $input = [];
        for ($index = 0; $index < 101; $index++) {
            $input[$operation === 'update' ? 1000 + $index : $index] = [
                'ENTITY_TYPE' => 'CONTACT', 'ENTITIES' => [$index + 1],
            ] + ($index % 2 === 0 ? ['WEBFORM_ID' => 0] : []);
        }

        $results = iterator_to_array($this->service($core)->{$operation}($input));
        self::assertSame([50, 50, 1], $packetSizes);
        self::assertCount(101, $observed);
        self::assertCount(101, $results);
        foreach ($results as $index => $result) {
            if ($operation === 'add') {
                self::assertInstanceOf(AddedItemBatchResult::class, $result);
                self::assertSame(1000 + $index, $result->getId());
            } else {
                self::assertInstanceOf(UpdatedItemBatchResult::class, $result);
                self::assertTrue($result->isSuccess());
            }
        }
    }

    public static function listOrders(): array
    {
        return ['ascending' => [['ID' => 'ASC']], 'descending' => [['ID' => 'DESC']],
            'arbitrary' => [['DATE_CREATE' => 'DESC', 'ID' => 'ASC']]];
    }

    #[DataProvider('listOrders')]
    public function testListYieldsMoreThanFiftyFlatRecordsWithoutPagination(array $order): void
    {
        $records = array_map(static fn (int $id): array => ['ID' => (string)$id], range(1, 75));
        if (($order['ID'] ?? null) === 'DESC') {
            $records = array_reverse($records);
        }

        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')
            ->with('crm.calllist.list', ['order' => $order, 'filter' => ['CREATED_BY_ID' => 1], 'select' => ['ID'], 'start' => 0])
            ->willReturn($this->response($records, 'crm.calllist.list'));
        $items = iterator_to_array($this->service($core)->list($order, ['CREATED_BY_ID' => 1], ['ID']));
        self::assertCount(75, $items);
        self::assertContainsOnlyInstancesOf(CallListItemResult::class, $items);
        self::assertSame(array_map('intval', array_column($records, 'ID')), array_map(static fn (CallListItemResult $item): int => $item->ID, $items));
    }

    public static function limits(): array
    {
        return ['zero' => [0], 'one' => [1], 'fifty' => [50], 'beyond-page' => [60], 'all' => [100]];
    }

    #[DataProvider('limits')]
    public function testListRespectsLimitWithoutPagination(int $limit): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')
            ->willReturn($this->response(array_map(static fn (int $id): array => ['ID' => $id], range(1, 75)), 'crm.calllist.list'));
        self::assertCount(min($limit, 75), iterator_to_array($this->service($core)->list(limit: $limit)));
    }

    #[DataProvider('operations')]
    public function testBatchApiErrorsPropagate(string $operation): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->willReturn($this->response([
            'result' => [], 'result_time' => [], 'result_error' => ['cmd_0' => ['error' => 'ACCESS_DENIED', 'error_description' => 'Call list denied']],
            'result_next' => [], 'result_total' => [],
        ], 'batch'));
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('call list denied');
        iterator_to_array($this->service($core)->{$operation}([42 => ['ENTITY_TYPE' => 'CONTACT', 'ENTITIES' => [1]]]));
    }

    #[DataProvider('operations')]
    public function testTransportFailurePreservesItsCause(string $operation): void
    {
        $failure = new \Bitrix24\SDK\Core\Exceptions\TransportException('Network unavailable');
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->willThrowException($failure);
        try {
            iterator_to_array($this->service($core)->{$operation}([42 => ['ENTITY_TYPE' => 'CONTACT', 'ENTITIES' => [1]]]));
            self::fail('Transport failure must propagate');
        } catch (BaseException $exception) {
            self::assertSame($failure, $exception->getPrevious());
        }
    }

    private function service(CoreInterface $core): Batch
    {
        $logger = new NullLogger();

        return new Batch(new CallListBatch($core, $logger), $logger);
    }
}
