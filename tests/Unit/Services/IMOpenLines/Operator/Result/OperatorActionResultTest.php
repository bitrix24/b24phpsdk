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

namespace Bitrix24\SDK\Tests\Unit\Services\IMOpenLines\Operator\Result;

use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Services\IMOpenLines\Operator\Result\OperatorActionResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OperatorActionResult::class)]
class OperatorActionResultTest extends TestCase
{
    public function testIsSuccessWithBooleanTrue(): void
    {
        $responseData = $this->createStub(ResponseData::class);
        $responseData->method('getResult')->willReturn([true]);

        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn($responseData);

        $operatorActionResult = new OperatorActionResult($response);
        
        self::assertTrue($operatorActionResult->isSuccess());
    }

    public function testIsSuccessWithBooleanFalse(): void
    {
        $responseData = $this->createStub(ResponseData::class);
        $responseData->method('getResult')->willReturn([false]);
        
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn($responseData);
        
        $operatorActionResult = new OperatorActionResult($response);
        
        self::assertFalse($operatorActionResult->isSuccess());
    }

    public function testIsSuccessWithArrayTrue(): void
    {
        $responseData = $this->createStub(ResponseData::class);
        $responseData->method('getResult')->willReturn([true]);
        
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn($responseData);
        
        $operatorActionResult = new OperatorActionResult($response);
        
        self::assertTrue($operatorActionResult->isSuccess());
    }

    public function testIsSuccessWithArrayFalse(): void
    {
        $responseData = $this->createStub(ResponseData::class);
        $responseData->method('getResult')->willReturn([false]);
        
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn($responseData);
        
        $operatorActionResult = new OperatorActionResult($response);
        
        self::assertFalse($operatorActionResult->isSuccess());
    }

    public function testIsSuccessWithNumericOne(): void
    {
        $responseData = $this->createStub(ResponseData::class);
        $responseData->method('getResult')->willReturn([1]);
        
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn($responseData);
        
        $operatorActionResult = new OperatorActionResult($response);
        
        self::assertTrue($operatorActionResult->isSuccess());
    }

    public function testIsSuccessWithNumericZero(): void
    {
        $responseData = $this->createStub(ResponseData::class);
        $responseData->method('getResult')->willReturn([0]);
        
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn($responseData);
        
        $operatorActionResult = new OperatorActionResult($response);
        
        self::assertFalse($operatorActionResult->isSuccess());
    }

    public function testIsSuccessWithStringTrue(): void
    {
        $responseData = $this->createStub(ResponseData::class);
        $responseData->method('getResult')->willReturn(['1']);
        
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn($responseData);
        
        $operatorActionResult = new OperatorActionResult($response);
        
        self::assertTrue($operatorActionResult->isSuccess());
    }

    public function testIsSuccessWithEmptyString(): void
    {
        $responseData = $this->createStub(ResponseData::class);
        $responseData->method('getResult')->willReturn(['']);
        
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn($responseData);
        
        $operatorActionResult = new OperatorActionResult($response);
        
        self::assertFalse($operatorActionResult->isSuccess());
    }

    public function testIsSuccessWithNull(): void
    {
        $responseData = $this->createStub(ResponseData::class);
        $responseData->method('getResult')->willReturn([null]);
        
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn($responseData);
        
        $operatorActionResult = new OperatorActionResult($response);
        
        self::assertFalse($operatorActionResult->isSuccess());
    }
}