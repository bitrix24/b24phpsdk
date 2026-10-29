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

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Batch;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiClientInterface;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\OperationTimeLimitExceededException;
use Bitrix24\SDK\Core\Response\Response;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Throwable;

#[CoversClass(Batch::class)]
class BatchOperationTimeLimitTest extends TestCase
{
    #[Test]
    public function testUpdateEntityItemsRethrowsOperationTimeLimitUnwrapped(): void
    {
        $batch = $this->batch('OPERATION_TIME_LIMIT');
        $throwable = $this->drain($batch->updateEntityItems('crm.deal.update', [1 => ['fields' => ['TITLE' => 'a']]]));

        $this->assertSame(OperationTimeLimitExceededException::class, $throwable::class);
    }

    #[Test]
    public function testAddEntityItemsRethrowsOperationTimeLimitUnwrapped(): void
    {
        $batch = $this->batch('OPERATION_TIME_LIMIT');
        $throwable = $this->drain($batch->addEntityItems('crm.deal.add', [['fields' => ['TITLE' => 'a']]]));

        $this->assertSame(OperationTimeLimitExceededException::class, $throwable::class);
    }

    #[Test]
    public function testDeleteEntityItemsRethrowsOperationTimeLimitUnwrapped(): void
    {
        $batch = $this->batch('OPERATION_TIME_LIMIT');
        $throwable = $this->drain($batch->deleteEntityItems('crm.deal.delete', [1]));

        $this->assertSame(OperationTimeLimitExceededException::class, $throwable::class);
    }

    #[Test]
    public function testOtherErrorsStillWrapped(): void
    {
        $batch = $this->batch('ACCESS_DENIED');
        $throwable = $this->drain($batch->updateEntityItems('crm.deal.update', [1 => ['fields' => ['TITLE' => 'a']]]));

        $this->assertSame(BaseException::class, $throwable::class);
        $this->assertStringStartsWith('batch update entity items:', $throwable->getMessage());
    }

    private function drain(Generator $generator): Throwable
    {
        try {
            foreach ($generator as $ignored) {
                // iterate to send the batch request
            }
        } catch (Throwable $throwable) {
            return $throwable;
        }

        $this->fail('exception expected');
    }

    private function batch(string $errorCode): Batch
    {
        $core = new class ($errorCode) implements CoreInterface {
            public function __construct(private readonly string $errorCode)
            {
            }

            public function call(string $apiMethod, array $parameters = []): Response
            {
                $commandIds = array_keys($parameters['cmd']);
                $body = [
                    'result' => [
                        'result' => [],
                        'result_error' => [$commandIds[0] => ['error' => $this->errorCode, 'error_description' => 'error']],
                        'result_total' => [],
                        'result_next' => [],
                        'result_time' => [],
                    ],
                    'time' => [
                        'start' => 1,
                        'finish' => 2,
                        'duration' => 1,
                        'processing' => 0,
                        'date_start' => '2026-01-01T00:00:00+00:00',
                        'date_finish' => '2026-01-01T00:00:01+00:00',
                    ],
                ];
                $httpResponse = (new MockHttpClient(new MockResponse((string)json_encode($body))))->request('POST', 'https://example.com/rest/');

                return new Response($httpResponse, new Command($apiMethod, $parameters), new ApiLevelErrorHandler(new NullLogger()), new NullLogger());
            }

            public function getApiClient(): ApiClientInterface
            {
                throw new \LogicException('not used');
            }
        };

        return new Batch($core, new NullLogger());
    }
}
