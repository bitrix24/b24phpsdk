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
class MessageAttachFormatTest extends MessageChatTestCase
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts ATTACH full form with metadata and blocks')]
    public function testAddMessageWithFullAttachForm(): void
    {
        $messageId = $this->sendAttach(
            attach: $this->createFullAttach(
                blocks: [
                    ['MESSAGE' => 'Full attach form payload'],
                ],
                id: 1,
                colorToken: 'primary',
                color: '#29619b',
            ),
            message: 'Full attach form',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts ATTACH short form without metadata wrapper')]
    public function testAddMessageWithShortAttachForm(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['MESSAGE' => 'Short attach form payload'],
            ],
            message: 'Short attach form',
        );

        $this->assertGreaterThan(0, $messageId);
    }
}
