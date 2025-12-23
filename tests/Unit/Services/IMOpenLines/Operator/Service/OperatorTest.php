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

namespace Bitrix24\SDK\Tests\Unit\Services\IMOpenLines\Operator\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\IMOpenLines\Operator\Result\OperatorActionResult;
use Bitrix24\SDK\Services\IMOpenLines\Operator\Service\Operator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Operator::class)]
class OperatorTest extends TestCase
{
    private Operator $operatorService;

    private CoreInterface&MockObject $coreMock;

    #[\Override]
    protected function setUp(): void
    {
        $this->coreMock = $this->createMock(CoreInterface::class);
        $this->operatorService = new Operator($this->coreMock, new NullLogger());
    }

    public function testAnswerCallsCorrectApiMethod(): void
    {
        $chatId = 12345;
        $responseMock = $this->createMock(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('imopenlines.operator.answer', ['CHAT_ID' => $chatId])
            ->willReturn($responseMock);

        $operatorActionResult = $this->operatorService->answer($chatId);

        self::assertInstanceOf(OperatorActionResult::class, $operatorActionResult);
    }

    public function testFinishCallsCorrectApiMethod(): void
    {
        $chatId = 12345;
        $responseMock = $this->createMock(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('imopenlines.operator.finish', ['CHAT_ID' => $chatId])
            ->willReturn($responseMock);

        $operatorActionResult = $this->operatorService->finish($chatId);

        self::assertInstanceOf(OperatorActionResult::class, $operatorActionResult);
    }

    public function testAnotherFinishCallsCorrectApiMethod(): void
    {
        $chatId = 12345;
        $responseMock = $this->createMock(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('imopenlines.operator.another.finish', ['CHAT_ID' => $chatId])
            ->willReturn($responseMock);

        $operatorActionResult = $this->operatorService->anotherFinish($chatId);

        self::assertInstanceOf(OperatorActionResult::class, $operatorActionResult);
    }

    public function testSkipCallsCorrectApiMethod(): void
    {
        $chatId = 12345;
        $responseMock = $this->createMock(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('imopenlines.operator.skip', ['CHAT_ID' => $chatId])
            ->willReturn($responseMock);

        $operatorActionResult = $this->operatorService->skip($chatId);

        self::assertInstanceOf(OperatorActionResult::class, $operatorActionResult);
    }

    public function testSpamCallsCorrectApiMethod(): void
    {
        $chatId = 12345;
        $responseMock = $this->createMock(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('imopenlines.operator.spam', ['CHAT_ID' => $chatId])
            ->willReturn($responseMock);

        $operatorActionResult = $this->operatorService->spam($chatId);

        self::assertInstanceOf(OperatorActionResult::class, $operatorActionResult);
    }

    public function testTransferWithOperatorIdCallsCorrectApiMethod(): void
    {
        $chatId = 12345;
        $operatorId = 67890;
        $responseMock = $this->createMock(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('imopenlines.operator.transfer', [
                'CHAT_ID' => $chatId,
                'TRANSFER_ID' => $operatorId
            ])
            ->willReturn($responseMock);

        $operatorActionResult = $this->operatorService->transfer($chatId, $operatorId);

        self::assertInstanceOf(OperatorActionResult::class, $operatorActionResult);
    }

    public function testTransferWithQueueCodeCallsCorrectApiMethod(): void
    {
        $chatId = 12345;
        $queueCode = 'queue#123#';
        $responseMock = $this->createMock(Response::class);

        $this->coreMock
            ->expects($this->once())
            ->method('call')
            ->with('imopenlines.operator.transfer', [
                'CHAT_ID' => $chatId,
                'TRANSFER_ID' => $queueCode
            ])
            ->willReturn($responseMock);

        $operatorActionResult = $this->operatorService->transfer($chatId, $queueCode);

        self::assertInstanceOf(OperatorActionResult::class, $operatorActionResult);
    }
}