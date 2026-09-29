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
class MessageMenuTest extends MessageChatTestCase
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts menu payloads with link and action items')]
    public function testAddMessageWithMenu(): void
    {
        $messageId = $this->sendMessage(
            message: 'Choose an action in menu',
            menu: [
                'ITEMS' => [
                    [
                        'TEXT' => 'Open site',
                        'LINK' => 'https://www.example.ru/',
                    ],
                    [
                        'TEXT' => 'Send text',
                        'ACTION' => 'SEND',
                        'ACTION_VALUE' => 'Ready',
                    ],
                ],
            ],
        );

        $this->assertGreaterThan(0, $messageId);
    }
}
