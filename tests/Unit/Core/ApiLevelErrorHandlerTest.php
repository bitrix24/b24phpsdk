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
use Bitrix24\SDK\Core\Exceptions\AuthForbiddenException;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\ItemNotFoundException;
use Bitrix24\SDK\Core\Exceptions\MethodNotFoundException;
use Bitrix24\SDK\Core\Exceptions\OperationTimeLimitExceededException;
use Bitrix24\SDK\Core\Exceptions\PaymentRequiredException;
use Bitrix24\SDK\Core\Exceptions\QueryLimitExceededException;
use Bitrix24\SDK\Core\Exceptions\UnknownScopeCodeException;
use Bitrix24\SDK\Core\Exceptions\ValidationException;
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

        // API v1 format: error is a plain string
        yield 'v1 - access denied' => [
            ['error' => 'ACCESS_DENIED', 'error_description' => 'Access denied!'],
            new AuthForbiddenException(),
        ];

        yield 'v1 - query limit exceeded' => [
            ['error' => 'QUERY_LIMIT_EXCEEDED', 'error_description' => 'Too many requests'],
            new QueryLimitExceededException(),
        ];

        yield 'v1 - method not found' => [
            ['error' => 'ERROR_METHOD_NOT_FOUND', 'error_description' => 'Unknown method called'],
            new MethodNotFoundException(),
        ];

        yield 'v1 - item not found' => [
            ['error' => 'NOT_FOUND', 'error_description' => 'Item not found'],
            new ItemNotFoundException(),
        ];

        // API v3 format: error is an array {"code": "...", "message": "..."}
        yield 'v3 - unknown dto property' => [
            ['error' => ['code' => 'BITRIX_REST_V3_EXCEPTION_UNKNOWNDTOPROPERTYEXCEPTION', 'message' => 'Unknown property TITLE']],
            new InvalidArgumentException(),
        ];

        yield 'v3 - access denied' => [
            ['error' => ['code' => 'ACCESS_DENIED', 'message' => 'Access denied!']],
            new AuthForbiddenException(),
        ];

        yield 'v3 - query limit exceeded' => [
            ['error' => ['code' => 'QUERY_LIMIT_EXCEEDED', 'message' => 'Too many requests']],
            new QueryLimitExceededException(),
        ];

        yield 'v3 - item not found' => [
            ['error' => ['code' => 'NOT_FOUND', 'message' => 'Item not found']],
            new ItemNotFoundException(),
        ];

        yield 'v3 - success response without error key' => [
            ['result' => ['id' => 42], 'time' => []],
            null,
        ];

        yield 'v3 - validation error with single field' => [
            [
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid input',
                    'validation' => [
                        ['field' => 'title', 'message' => 'Required field'],
                    ],
                ],
            ],
            new ValidationException(),
        ];

        yield 'v3 - validation error with multiple fields' => [
            [
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid input',
                    'validation' => [
                        ['field' => 'title', 'message' => 'Required field'],
                        ['field' => 'status', 'message' => 'Invalid value'],
                    ],
                ],
            ],
            new ValidationException(),
        ];

        yield 'v3 - error without validation field uses existing routing' => [
            ['error' => ['code' => 'ACCESS_DENIED', 'message' => 'Access denied!']],
            new AuthForbiddenException(),
        ];

        yield 'v3 - unknown error code without validation falls back to BaseException' => [
            ['error' => ['code' => 'SOME_UNKNOWN_CODE', 'message' => 'Something happened']],
            new BaseException(),
        ];
    }

    #[Test]
    #[TestDox('ValidationException carries field-level validation errors from v3 response')]
    public function testValidationExceptionCarriesValidationErrors(): void
    {
        $responseBody = [
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Invalid input',
                'validation' => [
                    ['field' => 'title', 'message' => 'Required field'],
                    ['field' => 'status', 'message' => 'Invalid value'],
                ],
            ],
        ];

        try {
            $this->apiLevelErrorHandler->handle($responseBody);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $errors = $e->getValidationErrors();
            $this->assertCount(2, $errors);
            $this->assertSame('title', $errors[0]->field);
            $this->assertSame('Required field', $errors[0]->message);
            $this->assertSame('status', $errors[1]->field);
            $this->assertSame('Invalid value', $errors[1]->message);
        }
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->apiLevelErrorHandler = new ApiLevelErrorHandler(new NullLogger());
    }
}
