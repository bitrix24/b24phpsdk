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
class MessageAttachDelimiterBlockTest extends MessageChatTestCase
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts DELIMITER block with default settings')]
    public function testAddAttachDelimiterBlockDefault(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['MESSAGE' => 'Before delimiter'],
                ['DELIMITER' => []],
                ['MESSAGE' => 'After delimiter'],
            ],
            message: 'DELIMITER block default',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts DELIMITER block with size only')]
    public function testAddAttachDelimiterBlockWithSize(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['MESSAGE' => 'Before delimiter'],
                ['DELIMITER' => [
                    'SIZE' => 200,
                ]],
                ['MESSAGE' => 'After delimiter'],
            ],
            message: 'DELIMITER block size',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts DELIMITER block with size and color')]
    public function testAddAttachDelimiterBlockWithSizeAndColor(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['MESSAGE' => 'Before delimiter'],
                ['DELIMITER' => [
                    'SIZE' => 200,
                    'COLOR' => '#c6c6c6',
                ]],
                ['MESSAGE' => 'After delimiter'],
            ],
            message: 'DELIMITER block color',
        );

        $this->assertGreaterThan(0, $messageId);
    }
}
