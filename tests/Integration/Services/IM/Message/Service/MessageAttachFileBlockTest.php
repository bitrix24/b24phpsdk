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
class MessageAttachFileBlockTest extends MessageChatTestCase
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts FILE block with minimal link only')]
    public function testAddAttachFileBlockMinimal(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['FILE' => [
                    [
                        'LINK' => self::DOCS_FILE_URL,
                    ],
                ]],
            ],
            message: 'FILE block minimal',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts FILE block with name and size')]
    public function testAddAttachFileBlockWithNameAndSize(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['FILE' => [
                    [
                        'NAME' => 'attach-preview.png',
                        'LINK' => self::DOCS_FILE_URL,
                        'SIZE' => 1500000,
                    ],
                ]],
            ],
            message: 'FILE block full',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts FILE block with multiple files')]
    public function testAddAttachFileBlockMultipleFiles(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['FILE' => [
                    [
                        'NAME' => 'attach-preview-1.png',
                        'LINK' => self::DOCS_FILE_URL,
                        'SIZE' => 1500000,
                    ],
                    [
                        'NAME' => 'attach-preview-2.png',
                        'LINK' => self::DOCS_FILE_URL,
                        'SIZE' => 1600000,
                    ],
                ]],
            ],
            message: 'FILE block multiple',
        );

        $this->assertGreaterThan(0, $messageId);
    }
}
