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

namespace Bitrix24\SDK\Tests\Unit\Services\Mail\MailboxField\Service;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Mail\MailboxField\Result\MailboxFieldResult;
use Bitrix24\SDK\Services\Mail\MailboxField\Result\MailboxFieldsResult;
use Bitrix24\SDK\Services\Mail\MailboxField\Service\MailboxField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(MailboxField::class)]
class MailboxFieldTest extends TestCase
{
    #[Test]
    public function testGetCallsMailboxFieldGetWithV3Api(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with(
                'mail.mailbox.field.get',
                [
                    'name' => 'email',
                    'select' => ['name', 'type', 'title'],
                ],
                ApiVersion::v3
            )
            ->willReturn($response);

        $this->assertInstanceOf(
            MailboxFieldResult::class,
            (new MailboxField($core, new NullLogger()))->get('email', ['name', 'type', 'title'])
        );
    }

    #[Test]
    public function testListCallsMailboxFieldListWithV3Api(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with(
                'mail.mailbox.field.list',
                ['select' => ['name', 'type']],
                ApiVersion::v3
            )
            ->willReturn($response);

        $this->assertInstanceOf(
            MailboxFieldsResult::class,
            (new MailboxField($core, new NullLogger()))->list(['name', 'type'])
        );
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        (new MailboxField($this->createStub(CoreInterface::class), new NullLogger()))->get('');
    }
}
