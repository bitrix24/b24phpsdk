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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Message\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Message\Service\Message;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Message::class)]
class MessageAttachUserBlockTest extends MessageChatTestCase
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts USER block with minimum required name')]
    public function testAddAttachUserBlockMinimal(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['USER' => [
                    'NAME' => 'Ivan Ivanov',
                ]],
            ],
            message: 'USER block minimal',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts USER block with avatar and external link')]
    public function testAddAttachUserBlockWithAvatarAndLink(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['USER' => [
                    'NAME' => 'Ivan Ivanov',
                    'AVATAR' => $this->currentUserPhotoUrl !== '' ? $this->currentUserPhotoUrl : self::DOCS_IMAGE_URL,
                    'LINK' => self::DOCS_LINK_URL,
                ]],
            ],
            message: 'USER block with avatar and link',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts USER block navigation variants via user chat bot and network identifiers')]
    public function testAddAttachUserBlockEntityVariants(): void
    {
        $chatId = $this->createChat();

        $messageId = $this->sendAttach(
            attach: [
                ['USER' => [
                    'NAME' => 'Portal user',
                    'USER_ID' => $this->currentUserId,
                    'AVATAR_TYPE' => 'USER',
                ]],
                ['USER' => [
                    'NAME' => 'Payload chat',
                    'CHAT_ID' => $chatId,
                    'AVATAR_TYPE' => 'CHAT',
                ]],
                ['USER' => [
                    'NAME' => 'Payload bot',
                    'BOT_ID' => 1,
                    'AVATAR_TYPE' => 'BOT',
                ]],
                ['USER' => [
                    'NAME' => 'Network user',
                    'NETWORK_ID' => 'network-user-example',
                ]],
            ],
            message: 'USER block entity variants',
        );

        $this->assertGreaterThan(0, $messageId);
    }
}
