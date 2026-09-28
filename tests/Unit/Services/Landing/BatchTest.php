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

namespace Bitrix24\SDK\Tests\Unit\Services\Landing;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Landing\Batch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
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
    #[TestDox('list by offset makes a single call when the first page is not full')]
    public function testListByOffsetSinglePage(): void
    {
        $batch = new Batch($this->makeCore(3), new NullLogger());

        $items = iterator_to_array($batch->getTraversableListByOffset(
            'landing.site.getList',
            ['select' => [], 'filter' => ['=DELETED' => 'N'], 'order' => []]
        ), false);

        $this->assertCount(3, $items);
        $this->assertCount(1, $this->directCalls);
        $this->assertSame([], $this->batchCommands);
        $this->assertSame(
            [
                'params' => [
                    'filter' => ['=DELETED' => 'N'],
                    'order' => ['ID' => 'ASC'],
                    'limit' => 50,
                    'offset' => 0,
                ],
            ],
            $this->directCalls[0][1]
        );
    }

    #[Test]
    #[TestDox('list by offset requests next pages with batch and stops on incomplete page')]
    public function testListByOffsetSeveralPages(): void
    {
        $batch = new Batch($this->makeCore(120), new NullLogger());

        $items = iterator_to_array($batch->getTraversableListByOffset('landing.landing.getList'), false);

        $this->assertCount(120, $items);
        $this->assertSame(range(1, 120), array_map(static fn(array $item): int => $item['ID'], $items));
        $this->assertCount(1, $this->directCalls);
        $this->assertCount(50, $this->batchCommands);
        $this->assertSame('50', $this->batchCommands[0][1]['params']['offset']);
        $this->assertSame('100', $this->batchCommands[1][1]['params']['offset']);
        $this->assertSame(['ID' => 'ASC'], $this->batchCommands[0][1]['params']['order']);
    }

    #[Test]
    #[TestDox('list by offset keeps custom order and adds ID as a tie-breaker')]
    public function testListByOffsetKeepsCustomOrder(): void
    {
        $batch = new Batch($this->makeCore(1), new NullLogger());

        iterator_to_array($batch->getTraversableListByOffset('landing.site.getList', ['order' => ['TITLE' => 'DESC']]));

        $this->assertSame(['TITLE' => 'DESC', 'ID' => 'ASC'], $this->directCalls[0][1]['params']['order']);
    }

    #[Test]
    #[TestDox('list by offset stops when limit is reached and registers only needed pages')]
    public function testListByOffsetWithLimit(): void
    {
        $batch = new Batch($this->makeCore(500), new NullLogger());

        $items = iterator_to_array($batch->getTraversableListByOffset('landing.site.getList', [], 120), false);

        $this->assertCount(120, $items);
        $this->assertCount(2, $this->batchCommands);
    }

    #[Test]
    #[TestDox('list by offset reads more than one batch packet')]
    public function testListByOffsetSeveralBatchPackets(): void
    {
        $batch = new Batch($this->makeCore(2600), new NullLogger());

        $items = iterator_to_array($batch->getTraversableListByOffset('landing.site.getList'), false);

        $this->assertCount(2600, $items);
        $this->assertCount(100, $this->batchCommands);
    }

    #[Test]
    #[TestDox('list by commands yields elements of every command result')]
    public function testListByCommands(): void
    {
        $batch = new Batch($this->makeCore(2), new NullLogger());

        $items = iterator_to_array($batch->getTraversableListByCommands('landing.role.getList', [[], ['scope' => 'GROUP']]), false);

        $this->assertCount(4, $items);
        $this->assertSame([], $this->batchCommands[0][1]);
        $this->assertSame(['scope' => 'GROUP'], $this->batchCommands[1][1]);
    }

    /**
     * @param array<int, int|string> $ids
     * @param array<string, mixed> $expectedParameters
     */
    #[Test]
    #[DataProvider('deleteKeyDataProvider')]
    #[TestDox('delete uses the entity key expected by the REST method')]
    public function testDeleteUsesEntityKey(string $apiMethod, array $ids, array $expectedParameters): void
    {
        $batch = new Batch($this->makeCore(0), new NullLogger());

        iterator_to_array($batch->deleteEntityItems($apiMethod, $ids));

        $this->assertSame($apiMethod, $this->batchCommands[0][0]);
        $this->assertSame($expectedParameters, $this->batchCommands[0][1]);
    }

    public static function deleteKeyDataProvider(): \Generator
    {
        yield 'site delete uses id' => ['landing.site.delete', [5], ['id' => '5']];
        yield 'page delete uses lid' => ['landing.landing.delete', [7], ['lid' => '7']];
        yield 'demos unregister uses code' => ['landing.demos.unregister', ['tpl'], ['code' => 'tpl']];
        yield 'repo unregister uses code' => ['landing.repo.unregister', ['block'], ['code' => 'block']];
        yield 'repowidget unregister uses code' => ['landing.repowidget.unregister', ['widget'], ['code' => 'widget']];
    }

    #[Test]
    #[TestDox('delete throws exception on invalid entity id type')]
    public function testDeleteThrowsOnInvalidIdType(): void
    {
        $batch = new Batch($this->makeCore(0), new NullLogger());

        $this->expectException(InvalidArgumentException::class);
        iterator_to_array($batch->deleteEntityItems('landing.site.delete', ['5']));
    }

    #[Test]
    #[TestDox('unregister throws exception on empty code')]
    public function testUnregisterThrowsOnEmptyCode(): void
    {
        $batch = new Batch($this->makeCore(0), new NullLogger());

        $this->expectException(InvalidArgumentException::class);
        iterator_to_array($batch->deleteEntityItems('landing.repo.unregister', ['']));
    }

    #[Test]
    #[TestDox('update uses lid for pages and id for sites')]
    public function testUpdateUsesEntityKey(): void
    {
        $batch = new Batch($this->makeCore(0), new NullLogger());

        iterator_to_array($batch->updateEntityItems('landing.landing.update', [7 => ['fields' => ['TITLE' => 'Page']]]));
        iterator_to_array($batch->updateEntityItems('landing.site.update', [5 => ['fields' => ['TITLE' => 'Site']]]));

        $this->assertSame(['lid' => '7', 'fields' => ['TITLE' => 'Page']], $this->batchCommands[0][1]);
        $this->assertSame(['id' => '5', 'fields' => ['TITLE' => 'Site']], $this->batchCommands[1][1]);
    }

    #[Test]
    #[TestDox('update throws exception when fields key is missing')]
    public function testUpdateThrowsWithoutFields(): void
    {
        $batch = new Batch($this->makeCore(0), new NullLogger());

        $this->expectException(InvalidArgumentException::class);
        iterator_to_array($batch->updateEntityItems('landing.site.update', [5 => ['TITLE' => 'Site']]));
    }

    #[Test]
    #[TestDox('process entity items registers parameters as is')]
    public function testProcessEntityItems(): void
    {
        $batch = new Batch($this->makeCore(0), new NullLogger());

        $results = iterator_to_array($batch->processEntityItems(
            'landing.syspage.set',
            [['id' => 1, 'type' => 'catalog', 'lid' => 2], ['id' => 1, 'type' => 'cart']]
        ), false);

        $this->assertCount(2, $results);
        $this->assertSame(['id' => '1', 'type' => 'catalog', 'lid' => '2'], $this->batchCommands[0][1]);
        $this->assertSame(['id' => '1', 'type' => 'cart'], $this->batchCommands[1][1]);
    }

    /**
     * Builds a CoreInterface stub emulating a landing list endpoint with $totalItems elements
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

                    return $this->makeResponse(['result' => $this->emulateCommand($apiMethod, $parameters, $totalItems), 'time' => $this->makeTime()]);
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
        if (str_ends_with(strtolower($apiMethod), 'getlist')) {
            $offset = (int)($parameters['params']['offset'] ?? 0);
            $limit = (int)($parameters['params']['limit'] ?? $totalItems);
            $items = [];
            for ($id = $offset + 1; $id <= min($offset + $limit, $totalItems); $id++) {
                $items[] = ['ID' => $id];
            }

            return $items;
        }

        return true;
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
