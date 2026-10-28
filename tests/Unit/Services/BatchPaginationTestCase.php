<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services;

use Bitrix24\SDK\Core\Batch;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use Bitrix24\SDK\Core\Response\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

abstract class BatchPaginationTestCase extends TestCase
{
    /** @var class-string<Batch> */
    protected const string BATCH_CLASS = Batch::class;
    protected const string API_METHOD = '';
    protected const string ENVELOPE = '';
    protected const string REQUEST_ID = 'id';

    #[DataProvider('paginationCases')]
    public function testListPreservesAllRecordsAndRequestedOrdering(?array $order, int $count, ?int $limit, ?array $select, ?array $filter): void
    {
        $rows = [];
        for ($i = 1; $i <= $count; ++$i) {
            $rows[] = ['id' => $i * 3, 'name' => sprintf('%03d', $count - $i), 'title' => sprintf('%03d', $count - $i)];
        }

        if ($filter !== null && static::REQUEST_ID === 'ID') {
            $filter = array_combine(array_map(static fn (string $field): string => preg_replace('/id$/i', 'ID', $field), array_keys($filter)), array_values($filter));
        }

        $requests = [];
        $core = $this->createStub(CoreInterface::class);
        $core->method('getAuthConnector')->willReturn(null);
        $core->method('call')->willReturnCallback(function (string $method, array $params) use ($rows, &$requests): Response {
            $requests[] = [$method, $params];
            self::assertLessThan((int) ceil(count($rows) / 50) + 10, count($requests), 'Pagination must terminate');
            if ($method !== 'batch') {
                [$result, $total, $next] = $this->page($rows, $params);
                return $this->response($method, $params, $result, $next, $total);
            }

            $results = [];
            $times = [];
            $totals = [];
            $nexts = [];
            foreach ($params['cmd'] as $commandId => $command) {
                [$subMethod, $query] = explode('?', $command, 2);
                self::assertSame(static::API_METHOD, $subMethod);
                parse_str($query, $arguments);
                array_walk_recursive($arguments, static function (&$value) use (&$results): void {
                    if (!is_string($value) || !str_starts_with($value, '$result[')) {
                        return;
                    }

                    preg_match_all('/\[([^\]]+)\]/', $value, $matches);
                    $resolved = $results;
                    foreach ($matches[1] as $key) {
                        self::assertArrayHasKey($key, $resolved, 'Invalid nested batch result reference: ' . $value);
                        $resolved = $resolved[$key];
                    }

                    $value = $resolved;
                });
                [$results[$commandId], $totals[$commandId], $nexts[$commandId]] = $this->page($rows, $arguments);
                $times[$commandId] = ['start' => 0, 'finish' => 0, 'duration' => 0, 'processing' => 0, 'date_start' => '2026-10-28T00:00:00Z', 'date_finish' => '2026-10-28T00:00:00Z'];
                $requests[] = [$subMethod, $arguments];
            }

            return $this->response($method, $params, ['result' => $results, 'result_time' => $times, 'result_total' => $totals, 'result_next' => $nexts], null, null);
        });
        $class = static::BATCH_CLASS;
        $batch = new $class($core, new NullLogger());
        $actual = iterator_to_array($batch->getTraversableList(static::API_METHOD, $order, $filter, $select, $limit, ['params' => ['withInfo' => 'yes']]), false);
        $expectedIds = array_map(static fn (int $id): int => $id * 3, $count === 0 ? [] : range(1, $count));
        $expectedIds = array_values(array_filter($expectedIds, static fn (int $id): bool => $id > ($filter['>' . static::REQUEST_ID] ?? 0)));

        $sort = $order ?? [];
        $field = strtolower((string) (array_key_first($sort) ?? 'id'));
        $direction = strtoupper((string) (array_values($sort)[0] ?? 'ASC'));
        if (($field === 'id' && $direction === 'DESC') || ($field !== 'id' && $direction === 'ASC')) {
            $expectedIds = array_reverse($expectedIds);
        }

        if ($limit !== null) {
            $expectedIds = array_slice($expectedIds, 0, $limit);
        }

        self::assertSame($expectedIds, array_column($actual, 'id'));
        self::assertCount(count($expectedIds), array_unique(array_column($actual, 'id')));
        foreach ($requests as [$method, $params]) {
            if ($method !== 'batch') {
                self::assertSame(['withInfo' => 'yes'], $params['params'], 'Additional parameters must reach every page');
                if (isset($filter['>=' . static::REQUEST_ID])) {
                    self::assertEquals($filter['>=' . static::REQUEST_ID], $params['filter']['>=' . static::REQUEST_ID]);
                }
            }
        }

        if ($count === 100 && $field === 'name') {
            $offsets = [];
            foreach ($requests as [$method, $params]) {
                if ($method === static::API_METHOD) {
                    $offsets[] = (int) ($params['start'] ?? 0);
                }
            }

            self::assertContains(100, $offsets, 'The paginator must handle an empty terminal page');
        }

        if ($select === []) {
            foreach ($actual as $row) {
                self::assertArrayHasKey('name', $row, 'Empty selection must retain default fields');
            }
        }
    }

    public static function paginationCases(): array
    {
        return [
            'ascending' => [['id' => 'asc'], 121, null, ['id', 'name'], []],
            'descending' => [['id' => 'desc'], 121, null, ['id'], []],
            'uppercase direction' => [['id' => 'ASC'], 121, null, ['id'], []],
            'uppercase ID key' => [['ID' => 'DESC'], 121, null, ['id'], []],
            'other sort' => [['name' => 'ASC'], 121, null, ['id', 'name'], []],
            'descending other sort' => [['name' => 'DESC'], 121, null, ['id', 'name'], []],
            'multiple batch packets' => [['ID' => 'DESC'], 2601, null, ['id'], []],
            'counted empty terminal page' => [['name' => 'ASC'], 100, null, ['id', 'name'], []],
            'empty' => [[], 0, null, [], []],
            'single page' => [[], 30, null, [], []],
            'default full selection' => [[], 121, null, [], []],
            'selected without ID' => [['id' => 'ASC'], 121, null, ['name'], []],
            'null arrays' => [null, 121, null, null, null],
            'limit zero' => [[], 121, 0, ['id'], []],
            'limit at page' => [[], 121, 50, ['id'], []],
            'limit over page' => [[], 121, 51, ['id'], []],
            'descending limit' => [['id' => 'DESC'], 121, 73, ['id'], []],
            'filtered' => [['id' => 'DESC'], 121, null, ['id'], ['>id' => 30, '>=id' => 33]],
        ];
    }

    private function page(array $rows, array $params): array
    {
        self::assertArrayHasKey('params', $params);
        self::assertEquals(['withInfo' => 'yes'], $params['params']);
        foreach ($params['filter'] ?? [] as $key => $value) {
            if (!preg_match('/^(>=|<=|>|<)(id)$/i', $key, $match)) {
                continue;
            }

            if (static::REQUEST_ID === 'ID' && $match[2] !== 'ID') {
                continue;
            }

            $rows = array_values(array_filter($rows, static fn (array $row): bool => match ($match[1]) {
                '>=' => $row['id'] >= $value,
                '<=' => $row['id'] <= $value,
                '>' => $row['id'] > $value,
                '<' => $row['id'] < $value,
                default => throw new \LogicException('Unsupported fixture comparison'),
            }));
        }

        $sort = $params['order'] ?? [];
        $field = strtolower((string) (array_key_first($sort) ?? 'id'));
        $direction = strtoupper((string) (array_values($sort)[0] ?? 'ASC'));
        usort($rows, static fn (array $left, array $right): int => ($direction === 'DESC' ? -1 : 1) * ($left[$field] <=> $right[$field]));
        $total = count($rows);
        $start = max(0, (int) ($params['start'] ?? 0));
        $page = array_slice($rows, $start, 50);
        if (($params['select'] ?? []) !== []) {
            $selection = array_fill_keys(array_map(strtolower(...), $params['select']), true);
            $page = array_map(static fn (array $row): array => array_intersect_key($row, $selection), $page);
        }

        return [[static::ENVELOPE => $page], $total, $start + 50 < $total ? $start + 50 : null];
    }

    private function response(string $method, array $params, array $result, ?int $next, ?int $total): Response
    {
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn(new ResponseData($result, Time::initWithZeroValues(), new Pagination($next, $total)));
        $response->method('getApiCommand')->willReturn(new Command($method, $params));
        return $response;
    }
}
