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

namespace Bitrix24\SDK\Tests\Unit\Services\Pull;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use Bitrix24\SDK\Core\Response\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

abstract class AbstractServiceContractTestCase extends TestCase
{
    #[DataProvider('calls')]
    public function testCallContract(string $class, string $method, array $arguments, string $endpoint, array $parameters): void
    {
        $response = $this->response([true]);
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($endpoint, self::callback(static function (array $actual) use ($parameters): bool {
            ksort($parameters);
            ksort($actual);

            return $actual === $parameters;
        }))->willReturn($response);
        self::assertSame($response, new $class($core, new NullLogger())->$method(...$arguments)->getCoreResponse());
    }

    #[DataProvider('calls')]
    public function testApiErrorsPropagate(string $class, string $method, array $arguments, string $endpoint, array $parameters): void
    {
        $baseException = new BaseException('Pull failed');
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($endpoint, self::callback(static function (array $actual) use ($parameters): bool {
            ksort($parameters);
            ksort($actual);

            return $actual === $parameters;
        }))->willThrowException($baseException);
        $this->expectExceptionObject($baseException);
        new $class($core, new NullLogger())->$method(...$arguments);
    }

    private function response(array $result): Response
    {
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn(new ResponseData($result, Time::initWithZeroValues(), new Pagination(null, null)));
        return $response;
    }
}
