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

namespace Bitrix24\SDK\Tests\Unit\Services\IM;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\IM\Batch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    /**
     * Direct (non-batch) calls: [apiMethod, parameters]
     *
     * @var array<int, array{0: string, 1: array<string, mixed>}>
     */
    private array $directCalls = [];

    /**
     * Batch sub-commands: [apiMethod, parsed parameters]
     *
     * @var array<int, array{0: string, 1: array<string, mixed>}>
     */
    private array $batchCommands = [];

    #[Test]
    #[TestDox('list by offset makes a single call when the first page is the last one')]
    public function testListByOffsetSinglePage(): void
    {
        $batch = new Batch($this->makeCore(3), new NullLogger());

        $items = iterator_to_array($batch->getTraversableListByOffset(
            'im.search.chat.list',
            ['FIND' => 'chat', 'FIND_LINES' => null]
        ), false);

        $this->assertCount(3, $items);
        $this->assertCount(1, $this->directCalls);
        $this->assertSame([], $this->batchCommands);
        $this->assertSame(
            ['FIND' => 'chat', 'OFFSET' => 0, 'LIMIT' => 50],
            $this->directCalls[0][1]
        );
    }

    #[Test]
    #[TestDox('list by offset registers only pages required by the total count')]
    public function testListByOffsetSeveralPagesWithTotal(): void
    {
        $batch = new Batch($this->makeCore(120), new NullLogger());

        $items = iterator_to_array($batch->getTraversableListByOffset(
            'im.dialog.users.list',
            ['DIALOG_ID' => 'chat1']
        ), false);

        $this->assertCount(120, $items);
        $this->assertSame(range(1, 120), array_map(static fn (array $item): int => $item['id'], $items));
        $this->assertCount(1, $this->directCalls);
        $this->assertCount(2, $this->batchCommands);
        $this->assertSame(['DIALOG_ID' => 'chat1', 'OFFSET' => '50', 'LIMIT' => '50'], $this->batchCommands[0][1]);
        $this->assertSame('100', $this->batchCommands[1][1]['OFFSET']);
    }

    #[Test]
    #[TestDox('list by offset reads more than one batch packet')]
    public function testListByOffsetSeveralBatchPackets(): void
    {
        $batch = new Batch($this->makeCore(2600), new NullLogger());

        $items = iterator_to_array($batch->getTraversableListByOffset('im.search.department.list'), false);

        $this->assertCount(2600, $items);
        $this->assertCount(51, $this->batchCommands);
    }

    #[Test]
    #[TestDox('list by offset stops when limit is reached and registers only needed pages')]
    public function testListByOffsetWithLimit(): void
    {
        $batch = new Batch($this->makeCore(500), new NullLogger());

        $items = iterator_to_array($batch->getTraversableListByOffset('im.department.colleagues.list', [], 120), false);

        $this->assertCount(120, $items);
        $this->assertCount(2, $this->batchCommands);
    }

    #[Test]
    #[TestDox('list by offset unwraps items of im.recent.list and follows the hasMore flag')]
    public function testListByOffsetRecentItems(): void
    {
        $batch = new Batch($this->makeCore(120), new NullLogger());

        $items = iterator_to_array($batch->getTraversableListByOffset('im.recent.list', ['SKIP_CHAT' => 'N']), false);

        $this->assertCount(120, $items);
        $this->assertSame(1, $items[0]['id']);
        $this->assertSame(120, $items[119]['id']);
        // total count is unknown, so the full batch packet is registered
        $this->assertCount(50, $this->batchCommands);
    }

    #[Test]
    #[TestDox('list by offset stops on im.recent.list full page without more pages')]
    public function testListByOffsetRecentFullLastPage(): void
    {
        $batch = new Batch($this->makeCore(50), new NullLogger());

        $items = iterator_to_array($batch->getTraversableListByOffset('im.recent.list'), false);

        $this->assertCount(50, $items);
        $this->assertSame([], $this->batchCommands);
    }

    #[Test]
    #[TestDox('list by offset drops user identifier keys of im.search.user.list')]
    public function testListByOffsetUsersKeyedById(): void
    {
        $batch = new Batch($this->makeCore(70), new NullLogger());

        $items = iterator_to_array($batch->getTraversableListByOffset('im.search.user.list', ['FIND' => 'user']), false);

        $this->assertCount(70, $items);
        $this->assertSame(1, $items[0]['id']);
        $this->assertSame(70, $items[69]['id']);
    }

    #[Test]
    #[TestDox('list by offset returns nothing for non-positive limit')]
    public function testListByOffsetWithZeroLimit(): void
    {
        $batch = new Batch($this->makeCore(10), new NullLogger());

        $items = iterator_to_array($batch->getTraversableListByOffset('im.search.chat.list', [], 0), false);

        $this->assertSame([], $items);
        $this->assertSame([], $this->directCalls);
    }

    #[Test]
    #[TestDox('process entity items registers flat parameters as is')]
    public function testProcessEntityItems(): void
    {
        $batch = new Batch($this->makeCore(0), new NullLogger());

        $results = iterator_to_array($batch->processEntityItems(
            'im.chat.user.delete',
            [['CHAT_ID' => 1, 'USER_ID' => 2], ['CHAT_ID' => 3, 'USER_ID' => 4]]
        ), false);

        $this->assertCount(2, $results);
        $this->assertSame([true], $results[0]->getResult());
        $this->assertSame(['CHAT_ID' => '1', 'USER_ID' => '2'], $this->batchCommands[0][1]);
        $this->assertSame(['CHAT_ID' => '3', 'USER_ID' => '4'], $this->batchCommands[1][1]);
    }

    /**
     * Builds a CoreInterface stub emulating im.* list endpoints with $totalItems elements
     * and returning true for any other command
     */
    private function makeCore(int $totalItems): CoreInterface
    {
        $this->directCalls = [];
        $this->batchCommands = [];

        $core = $this->createStub(CoreInterface::class);
        $core->method('getAuthConnector')->willReturn(null);
        $core->method('call')->willReturnCallback(
            function (string $apiMethod, array $parameters = []) use ($totalItems): Response {
                if ($apiMethod !== 'batch') {
                    $this->directCalls[] = [$apiMethod, $parameters];

                    return $this->makeResponse([
                        'result' => $this->emulateCommand($apiMethod, $parameters, $totalItems),
                        'total' => $apiMethod === 'im.recent.list' ? -1 : $totalItems,
                        'time' => $this->makeTime(),
                    ]);
                }

                $result = [];
                $resultTime = [];
                foreach ($parameters['cmd'] as $commandId => $command) {
                    [$commandMethod, $query] = array_pad(explode('?', (string)$command, 2), 2, '');
                    parse_str($query, $commandParameters);
                    $this->batchCommands[] = [$commandMethod, $commandParameters];

                    $result[$commandId] = $this->emulateCommand($commandMethod, $commandParameters, $totalItems);
                    $resultTime[$commandId] = $this->makeTime();
                }

                return $this->makeResponse([
                    'result' => [
                        'result' => $result,
                        'result_error' => [],
                        'result_total' => [],
                        'result_next' => [],
                        'result_time' => $resultTime,
                    ],
                    'time' => $this->makeTime(),
                ]);
            }
        );

        return $core;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function emulateCommand(string $apiMethod, array $parameters, int $totalItems): mixed
    {
        if (!str_ends_with($apiMethod, '.list') || $apiMethod === 'im.chat.user.list') {
            return true;
        }

        $offset = (int)($parameters['OFFSET'] ?? 0);
        $limit = (int)($parameters['LIMIT'] ?? $totalItems);
        $items = [];
        for ($id = $offset + 1; $id <= min($offset + $limit, $totalItems); $id++) {
            $items[$id] = ['id' => $id];
        }

        if ($apiMethod === 'im.recent.list') {
            return [
                'items' => array_values($items),
                'hasMorePages' => $offset + $limit < $totalItems,
                'hasMore' => $offset + $limit < $totalItems,
            ];
        }

        // im.search.user.list returns an object keyed by user identifier
        return $apiMethod === 'im.search.user.list' ? $items : array_values($items);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function makeResponse(array $body): Response
    {
        $httpResponse = (new MockHttpClient(new MockResponse((string)json_encode($body))))->request('POST', 'https://example.com/rest/');

        return new Response(
            $httpResponse,
            new Command('batch', []),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function makeTime(): array
    {
        return [
            'start' => 1,
            'finish' => 2,
            'duration' => 1,
            'processing' => 0,
            'date_start' => '2026-01-01T00:00:00+00:00',
            'date_finish' => '2026-01-01T00:00:01+00:00',
        ];
    }
}
