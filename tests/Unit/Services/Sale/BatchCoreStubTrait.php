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

namespace Bitrix24\SDK\Tests\Unit\Services\Sale;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Builds a CoreInterface stub for batch unit tests of Sale services.
 *
 * Every REST command (a direct call or a sub-command of a "batch" call) is recorded to $capturedCalls
 * as [apiMethod, parameters] and answered with the payload returned by the result provider.
 */
trait BatchCoreStubTrait
{
    /**
     * @var list<array{0: string, 1: array<mixed>}>
     */
    private array $capturedCalls = [];

    /**
     * @param callable(string, array<mixed>): mixed $resultProvider returns the "result" payload for a single command
     * @param int|null $total total elements count returned with every command, emulates list pagination
     */
    private function makeCore(callable $resultProvider, ?int $total = null): CoreInterface
    {
        $core = $this->createStub(CoreInterface::class);
        $core->method('call')->willReturnCallback(
            function (string $apiMethod, array $parameters = []) use ($resultProvider, $total): Response {
                if ($apiMethod === 'batch') {
                    $results = [];
                    $times = [];
                    $totals = [];
                    foreach ($parameters['cmd'] as $commandId => $command) {
                        [$commandMethod, $query] = array_pad(explode('?', (string)$command, 2), 2, '');
                        parse_str($query, $commandParameters);
                        $this->capturedCalls[] = [$commandMethod, $commandParameters];
                        $results[$commandId] = $resultProvider($commandMethod, $commandParameters);
                        $times[$commandId] = $this->makeTimeNode();
                        if ($total !== null) {
                            $totals[$commandId] = $total;
                        }
                    }

                    $body = [
                        'result' => [
                            'result' => $results,
                            'result_error' => [],
                            'result_total' => $totals,
                            'result_next' => [],
                            'result_time' => $times,
                        ],
                        'time' => $this->makeTimeNode(),
                    ];
                } else {
                    $this->capturedCalls[] = [$apiMethod, $parameters];
                    $body = [
                        'result' => $resultProvider($apiMethod, $parameters),
                        'time' => $this->makeTimeNode(),
                    ];
                    if ($total !== null) {
                        $body['total'] = $total;
                        $body['next'] = 50;
                    }
                }

                $mockHttpClient = new MockHttpClient(new MockResponse((string)json_encode($body)));

                return new Response(
                    $mockHttpClient->request('POST', 'https://example.bitrix24.com/rest/' . $apiMethod),
                    new Command($apiMethod, $parameters),
                    new ApiLevelErrorHandler(new NullLogger()),
                    new NullLogger()
                );
            }
        );

        return $core;
    }

    /**
     * @return array<string, float|string>
     */
    private function makeTimeNode(): array
    {
        return [
            'start' => 1700000000.0,
            'finish' => 1700000000.1,
            'duration' => 0.1,
            'processing' => 0.05,
            'date_start' => '2024-01-01T00:00:00+00:00',
            'date_finish' => '2024-01-01T00:00:00+00:00',
        ];
    }
}
