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
class MessageAttachGridBlockTest extends MessageChatTestCase
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts GRID block with BLOCK display')]
    public function testAddAttachGridBlockDisplayBlock(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['GRID' => [
                    [
                        'NAME' => 'Description',
                        'VALUE' => 'Structured entities in messenger messages',
                        'DISPLAY' => 'BLOCK',
                        'WIDTH' => 250,
                    ],
                    [
                        'NAME' => 'Category',
                        'VALUE' => 'Wish',
                        'DISPLAY' => 'BLOCK',
                        'WIDTH' => 100,
                    ],
                ]],
            ],
            message: 'GRID block BLOCK',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts GRID block with LINE display and color settings')]
    public function testAddAttachGridBlockDisplayLine(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['GRID' => [
                    [
                        'NAME' => 'Priority',
                        'VALUE' => 'High',
                        'COLOR_TOKEN' => 'alert',
                        'COLOR' => '#ff0000',
                        'DISPLAY' => 'LINE',
                        'WIDTH' => 250,
                    ],
                    [
                        'NAME' => 'Category',
                        'VALUE' => 'Wish',
                        'DISPLAY' => 'LINE',
                    ],
                ]],
            ],
            message: 'GRID block LINE',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts GRID block with ROW display')]
    public function testAddAttachGridBlockDisplayRow(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['GRID' => [
                    [
                        'NAME' => 'Priority',
                        'VALUE' => 'High',
                        'DISPLAY' => 'ROW',
                        'WIDTH' => 250,
                    ],
                    [
                        'NAME' => 'Category',
                        'VALUE' => 'Wish',
                        'DISPLAY' => 'ROW',
                    ],
                ]],
            ],
            message: 'GRID block ROW',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts GRID block with TABLE display')]
    public function testAddAttachGridBlockDisplayTable(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['GRID' => [
                    [
                        'NAME' => 'Project',
                        'VALUE' => 'BUGS',
                        'DISPLAY' => 'TABLE',
                    ],
                    [
                        'NAME' => 'Category',
                        'VALUE' => 'im',
                        'DISPLAY' => 'TABLE',
                    ],
                    [
                        'NAME' => 'Deadline',
                        'VALUE' => '04.11.2015 17:50:43',
                        'DISPLAY' => 'TABLE',
                    ],
                ]],
            ],
            message: 'GRID block TABLE',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts GRID block with BBCode values and entity links')]
    public function testAddAttachGridBlockWithBbCodeAndEntityLinks(): void
    {
        $chatId = $this->createChat();

        $messageId = $this->sendAttach(
            attach: [
                ['GRID' => [
                    [
                        'NAME' => 'Owner',
                        'VALUE' => sprintf('[USER=%d][B]Current user[/B][/USER]', $this->currentUserId),
                        'DISPLAY' => 'ROW',
                        'USER_ID' => $this->currentUserId,
                    ],
                    [
                        'NAME' => 'Chat',
                        'VALUE' => sprintf('[CHAT=%d]Payload chat[/CHAT]', $chatId),
                        'DISPLAY' => 'ROW',
                        'CHAT_ID' => $chatId,
                    ],
                    [
                        'NAME' => 'Action',
                        'VALUE' => '[PUT=/help]Insert help[/PUT][BR][URL=https://bitrix24.ru]Portal[/URL]',
                        'DISPLAY' => 'ROW',
                        'LINK' => self::DOCS_LINK_URL,
                        'HEIGHT' => 80,
                    ],
                ]],
            ],
            message: 'GRID block BBCode',
        );

        $this->assertGreaterThan(0, $messageId);
    }
}
