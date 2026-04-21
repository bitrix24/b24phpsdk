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
class MessageAttachLinkBlockTest extends MessageChatTestCase
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts LINK block with minimal external link payload')]
    public function testAddAttachLinkBlockMinimal(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['LINK' => [
                    'LINK' => self::DOCS_LINK_URL,
                ]],
            ],
            message: 'LINK block minimal',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts LINK block with preview name description and dimensions')]
    public function testAddAttachLinkBlockWithDescriptionAndPreview(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['LINK' => [
                    'PREVIEW' => self::DOCS_IMAGE_URL,
                    'WIDTH' => 1000,
                    'HEIGHT' => 638,
                    'NAME' => 'Ticket #12345: new messenger API',
                    'DESC' => 'Implement before release',
                    'LINK' => self::DOCS_LINK_URL,
                ]],
            ],
            message: 'LINK block with preview',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts LINK block with HTML description')]
    public function testAddAttachLinkBlockWithHtmlDescription(): void
    {
        $messageId = $this->sendAttach(
            attach: [
                ['LINK' => [
                    'NAME' => 'HTML link block',
                    'HTML' => '<strong>Rich description</strong> for attach link',
                    'LINK' => self::DOCS_LINK_URL,
                ]],
            ],
            message: 'LINK block with HTML',
        );

        $this->assertGreaterThan(0, $messageId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('add accepts LINK block navigation variants via user chat and network identifiers')]
    public function testAddAttachLinkBlockEntityVariants(): void
    {
        $chatId = $this->createChat();

        $messageId = $this->sendAttach(
            attach: [
                ['LINK' => [
                    'NAME' => 'Current user',
                    'USER_ID' => $this->currentUserId,
                ]],
                ['LINK' => [
                    'NAME' => 'Current chat',
                    'CHAT_ID' => $chatId,
                ]],
                ['LINK' => [
                    'NAME' => 'Network user',
                    'NETWORK_ID' => 'network-user-example',
                ]],
            ],
            message: 'LINK block entity variants',
        );

        $this->assertGreaterThan(0, $messageId);
    }
}
