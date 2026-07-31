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

namespace Bitrix24\SDK\Tests\Unit\Services\Mail\Service;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Mail\Result\MailboxResult;
use Bitrix24\SDK\Services\Mail\Result\MailboxesResult;
use Bitrix24\SDK\Services\Mail\Result\SendersResult;
use Bitrix24\SDK\Services\Mail\Service\Batch;
use Bitrix24\SDK\Services\Mail\Service\Mailbox;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Mailbox::class)]
class MailboxTest extends TestCase
{
    #[Test]
    public function testGetCallsMailboxGetWithV3Api(): void
    {
        $core = $this->createCoreExpectingCall(
            'mail.mailbox.get',
            [
                'id' => 17,
                'select' => ['id', 'name', 'email'],
            ]
        );

        $this->assertInstanceOf(
            MailboxResult::class,
            $this->createService($core)->get(17, ['id', 'name', 'email'])
        );
    }

    #[Test]
    public function testListCallsMailboxListWithV3Api(): void
    {
        $core = $this->createCoreExpectingCall(
            'mail.mailbox.list',
            [
                'name' => 'work',
                'email' => 'example.com',
                'pagination' => ['page' => 1, 'limit' => 20],
                'select' => ['id', 'email'],
                'filter' => ['id' => 17],
                'order' => ['id' => 'ASC'],
            ]
        );

        $this->assertInstanceOf(
            MailboxesResult::class,
            $this->createService($core)->list(
                name: 'work',
                email: 'example.com',
                pagination: ['page' => 1, 'limit' => 20],
                select: ['id', 'email'],
                filter: ['id' => 17],
                order: ['id' => 'ASC']
            )
        );
    }

    #[Test]
    public function testSendersCallsMailboxSendersWithV3Api(): void
    {
        $core = $this->createCoreExpectingCall(
            'mail.mailbox.senders',
            ['pagination' => ['page' => 1, 'limit' => 20]]
        );

        $this->assertInstanceOf(
            SendersResult::class,
            $this->createService($core)->senders(['page' => 1, 'limit' => 20])
        );
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function createCoreExpectingCall(string $method, array $parameters): CoreInterface
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with($method, $parameters, ApiVersion::v3)
            ->willReturn($response);

        return $core;
    }

    private function createService(CoreInterface $core): Mailbox
    {
        return new Mailbox(new Batch(new NullBatch(), new NullLogger()), $core, new NullLogger());
    }
}
