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

namespace Bitrix24\SDK\Tests\Unit\Services\Mail\MessageField\Service;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Mail\MessageField\Result\MessageFieldResult;
use Bitrix24\SDK\Services\Mail\MessageField\Result\MessageFieldsResult;
use Bitrix24\SDK\Services\Mail\MessageField\Service\MessageField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(MessageField::class)]
class MessageFieldTest extends TestCase
{
    #[Test]
    public function testGetCallsMessageFieldGetWithV3Api(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with(
                'mail.message.field.get',
                [
                    'name' => 'subject',
                    'select' => ['name', 'type', 'title'],
                ],
                ApiVersion::v3
            )
            ->willReturn($response);

        $this->assertInstanceOf(
            MessageFieldResult::class,
            (new MessageField($core, new NullLogger()))->get('subject', ['name', 'type', 'title'])
        );
    }

    #[Test]
    public function testListCallsMessageFieldListWithV3Api(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with(
                'mail.message.field.list',
                ['select' => ['name', 'type']],
                ApiVersion::v3
            )
            ->willReturn($response);

        $this->assertInstanceOf(
            MessageFieldsResult::class,
            (new MessageField($core, new NullLogger()))->list(['name', 'type'])
        );
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        (new MessageField($this->createStub(CoreInterface::class), new NullLogger()))->get('');
    }
}
