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
class MessageAttachImageBlockTest extends MessageChatTestCase
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts IMAGE block with a single minimal image item')]
    public function testAddAttachImageBlockMinimal(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['IMAGE' => [
                    [
                        'LINK' => self::DOCS_IMAGE_URL,
                    ],
                ]],
            ],
            message: 'IMAGE block minimal',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts IMAGE block with preview name and dimensions')]
    public function testAddAttachImageBlockFullVariant(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['IMAGE' => [
                    [
                        'NAME' => 'This is attach preview',
                        'LINK' => self::DOCS_IMAGE_URL,
                        'PREVIEW' => self::DOCS_IMAGE_URL,
                        'WIDTH' => 1000,
                        'HEIGHT' => 638,
                    ],
                ]],
            ],
            message: 'IMAGE block full',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts IMAGE block with multiple images')]
    public function testAddAttachImageBlockMultipleImages(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['IMAGE' => [
                    [
                        'NAME' => 'First image',
                        'LINK' => self::DOCS_IMAGE_URL,
                    ],
                    [
                        'NAME' => 'Second image',
                        'LINK' => self::DOCS_IMAGE_URL,
                        'PREVIEW' => self::DOCS_IMAGE_URL,
                        'WIDTH' => 600,
                        'HEIGHT' => 400,
                    ],
                ]],
            ],
            message: 'IMAGE block multiple',
        );

        $this->assertGreaterThan(0, $messageId);
    }
}
