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

namespace Bitrix24\SDK\Tests\Unit\Services\Mail\RecipientField\Service;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Mail\RecipientField\Result\RecipientFieldResult;
use Bitrix24\SDK\Services\Mail\RecipientField\Result\RecipientFieldsResult;
use Bitrix24\SDK\Services\Mail\RecipientField\Service\RecipientField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(RecipientField::class)]
class RecipientFieldTest extends TestCase
{
    #[Test]
    public function testGetCallsRecipientFieldGetWithV3Api(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with(
                'mail.recipient.field.get',
                [
                    'name' => 'email',
                    'select' => ['name', 'type', 'title'],
                ],
                ApiVersion::v3
            )
            ->willReturn($response);

        $this->assertInstanceOf(
            RecipientFieldResult::class,
            (new RecipientField($core, new NullLogger()))->get('email', ['name', 'type', 'title'])
        );
    }

    #[Test]
    public function testListCallsRecipientFieldListWithV3Api(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with(
                'mail.recipient.field.list',
                ['select' => ['name', 'type']],
                ApiVersion::v3
            )
            ->willReturn($response);

        $this->assertInstanceOf(
            RecipientFieldsResult::class,
            (new RecipientField($core, new NullLogger()))->list(['name', 'type'])
        );
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        (new RecipientField($this->createStub(CoreInterface::class), new NullLogger()))->get('');
    }
}
