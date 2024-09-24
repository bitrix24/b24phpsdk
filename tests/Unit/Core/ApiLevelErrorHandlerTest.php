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
use Bitrix24\SDK\Core\CoreBuilder;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\OperationTimeLimitExceededException;
use Bitrix24\SDK\Core\Exceptions\PaymentRequiredException;
use Bitrix24\SDK\Core\Exceptions\QueryLimitExceededException;
use Bitrix24\SDK\Core\Exceptions\UnknownScopeCodeException;
use Bitrix24\SDK\Core\Exceptions\WrongClientException;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Throwable;

#[CoversClass(ApiLevelErrorHandler::class)]
class ApiLevelErrorHandlerTest extends TestCase
{
    private ApiLevelErrorHandler $apiLevelErrorHandler;

    #[DataProvider('typicalErrorsDataProvider')]
    #[Test]
    #[TestDox('test init from constructor')]
    public function testErrorHandler(array $responseBody, ?Throwable $throwable): void
    {
        if ($throwable instanceof Throwable) {
                $this->expectException($throwable::class);
        }

        $this->apiLevelErrorHandler->handle($responseBody);
        // fix for happy path
        $this->assertTrue(true);
    }

    public static function typicalErrorsDataProvider(): Generator
    {
        yield 'single query - payment required' => [
            [
                "error" => "PAYMENT_REQUIRED",
                "error_description" => "Subscription has been ended",
            ],
            new PaymentRequiredException()
        ];

        yield 'single query - refresh token error' => [
            [
                "error" => "wrong_client",
            ],
            new WrongClientException()
        ];

        yield 'single query - without errors' => [
            [
                "result" => 3465,
                "time" => [
                    "start" => 1705764932.998683,
                    "finish" => 1705764937.173995,
                    "duration" => 4.1753120422363281,
                    "processing" => 3.3076529502868652,
                    "date_start" => "2024-01-20T18:35:32+03:00",
                    "date_finish" => "2024-01-20T18:35:37+03:00",
                    "operating_reset_at" => 1705765533,
                    "operating" => 3.3076241016387939
                ]
            ],
            null
        ];

        yield 'batch query - operation time limit' => [
            [
                'result' => [
                    'result' => [],
                    'result_error' => [
                        "592dcd1e-cd14-410f-bab5-76b3ede717dd" => [
                            'error' => 'OPERATION_TIME_LIMIT',
                            'error_description' => 'Method is blocked due to operation time limit.'
                        ]
                    ]
                ],
            ],
            new OperationTimeLimitExceededException()
        ];
    }

    protected function setUp(): void
    {
        $this->apiLevelErrorHandler = new ApiLevelErrorHandler(new NullLogger());
    }
}
