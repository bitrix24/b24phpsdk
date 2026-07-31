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
use Bitrix24\SDK\Services\Mail\Result\RecipientsResult;
use Bitrix24\SDK\Services\Mail\Service\Batch;
use Bitrix24\SDK\Services\Mail\Service\Recipient;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Recipient::class)]
class RecipientTest extends TestCase
{
    #[Test]
    public function testListContactsCallsRecipientListContactsWithV3Api(): void
    {
        $core = $this->createCoreExpectingCall(
            'mail.recipient.listcontacts',
            [
                'query' => 'client',
                'pagination' => ['page' => 1, 'limit' => 20],
            ]
        );

        $this->assertInstanceOf(
            RecipientsResult::class,
            $this->createService($core)->listContacts('client', ['page' => 1, 'limit' => 20])
        );
    }

    #[Test]
    public function testListEmployeesCallsRecipientListEmployeesWithV3Api(): void
    {
        $core = $this->createCoreExpectingCall(
            'mail.recipient.listemployees',
            [
                'query' => 'Ivan',
                'pagination' => ['page' => 1, 'limit' => 20],
            ]
        );

        $this->assertInstanceOf(
            RecipientsResult::class,
            $this->createService($core)->listEmployees('Ivan', ['page' => 1, 'limit' => 20])
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

    private function createService(CoreInterface $core): Recipient
    {
        return new Recipient(new Batch(new NullBatch(), new NullLogger()), $core, new NullLogger());
    }
}
