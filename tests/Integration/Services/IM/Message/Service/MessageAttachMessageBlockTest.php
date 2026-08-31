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
class MessageAttachMessageBlockTest extends MessageChatTestCase
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts MESSAGE block with plain text')]
    public function testAddAttachMessageBlockPlainText(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['MESSAGE' => 'API will be available in update im 24.0.0'],
            ],
            message: 'MESSAGE block plain text',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts MESSAGE block with all documented BBCode variants')]
    public function testAddAttachMessageBlockWithAllBbCodes(): void
    {
        $chatId = $this->createChat();

        $messageId = $this->sendAttach(
            attach: [
                ['MESSAGE' => implode('[BR]', [
                    sprintf('[USER=%d]Current user[/USER]', $this->currentUserId),
                    sprintf('[CHAT=%d]Payload chat[/CHAT]', $chatId),
                    '[SEND=/help]Send help[/SEND]',
                    '[PUT=/search]Put search[/PUT]',
                    '[CALL=+79991234567]Call us[/CALL]',
                    '[B]Bold[/B] [U]Underline[/U] [I]Italic[/I] [S]Strike[/S]',
                    '[URL=https://bitrix24.ru]Bitrix24[/URL]',
                ])],
            ],
            message: 'MESSAGE block BBCode',
        );

        $this->assertGreaterThan(0, $messageId);
    }
}
