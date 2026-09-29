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
class MessageFormattingTest extends MessageChatTestCase
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts basic BBCode formatting payloads')]
    public function testAddMessageWithBasicFormatting(): void
    {
        $messageId = $this->sendMessage(
            message: implode('[br]', [
                '[b]Bold[/b] [i]Italic[/i]',
                '[u]Underline[/u] [s]Strike[/s]',
                '[color=#ff0000]Red[/color] [size=20]Large[/size]',
            ]),
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts advanced BBCode formatting payloads')]
    public function testAddMessageWithAdvancedFormatting(): void
    {
        $chatId = $this->createChat();
        $dialogId = $this->getDialogId($chatId);

        $messageId = $this->messageService->add(
            dialogId: $dialogId,
            message: implode('[br]', [
                sprintf('[user=%d]Current user[/user] joined [chat=%d]payload chat[/chat]', $this->currentUserId, $chatId),
                '[url=https://bitrix24.ru]Bitrix24[/url]',
                '>>quoted line',
                '[code]payload::advanced();[/code]',
                sprintf('[timestamp=%d format=SHORT_TIME_FORMAT]', time()),
            ]),
        )->getId();

        $this->assertGreaterThan(0, $messageId);
    }
}
