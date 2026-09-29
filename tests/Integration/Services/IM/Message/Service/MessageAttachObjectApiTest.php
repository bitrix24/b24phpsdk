<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please see the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Message\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Message\Attach\Attach;
use Bitrix24\SDK\Services\IM\Message\Attach\Enums\AttachColorToken;
use Bitrix24\SDK\Services\IM\Message\Service\Message;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Message::class)]
class MessageAttachObjectApiTest extends MessageChatTestCase
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts ATTACH object short form')]
    public function testAddAcceptsAttachObjectShortForm(): void
    {
        $messageId = $this->sendMessage(
            message: 'Attach object short form',
            attach: Attach::create()->message('Short object payload'),
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts ATTACH object full form')]
    public function testAddAcceptsAttachObjectFullForm(): void
    {
        $messageId = $this->sendMessage(
            message: 'Attach object full form',
            attach: Attach::create()
                ->id(1)
                ->colorToken(AttachColorToken::primary)
                ->message('Full object payload'),
        );

        $this->assertGreaterThan(0, $messageId);
    }
}
