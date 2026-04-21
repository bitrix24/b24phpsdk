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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Message\Attach\Blocks;

use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\DelimiterBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\LinkBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\MessageBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\UserBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Enums\AttachAvatarType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageBlock::class)]
#[CoversClass(LinkBlock::class)]
#[CoversClass(UserBlock::class)]
#[CoversClass(DelimiterBlock::class)]
final class SimpleBlocksTest extends TestCase
{
    #[Test]
    public function messageBlockSerializesText(): void
    {
        self::assertSame(
            ['MESSAGE' => 'Hello'],
            MessageBlock::text('Hello')->build()
        );
    }

    #[Test]
    public function messageBlockRejectsEmptyText(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        MessageBlock::text('');
    }

    #[Test]
    public function linkBlockSerializesOptionalFields(): void
    {
        $linkBlock = LinkBlock::url('https://apidocs.bitrix24.ru')
            ->name('Docs')
            ->description('Open docs')
            ->html('<strong>Docs</strong>')
            ->preview('https://example.com/preview.png')
            ->width(1000)
            ->height(638);

        self::assertSame(
            [
                'LINK' => [
                    'LINK' => 'https://apidocs.bitrix24.ru',
                    'NAME' => 'Docs',
                    'DESC' => 'Open docs',
                    'HTML' => '<strong>Docs</strong>',
                    'PREVIEW' => 'https://example.com/preview.png',
                    'WIDTH' => 1000,
                    'HEIGHT' => 638,
                ],
            ],
            $linkBlock->build()
        );
    }

    #[Test]
    public function linkBlockSerializesMinimalUrlPayload(): void
    {
        self::assertSame(
            [
                'LINK' => [
                    'LINK' => 'https://apidocs.bitrix24.ru',
                ],
            ],
            LinkBlock::url('https://apidocs.bitrix24.ru')->build()
        );
    }

    #[Test]
    public function linkBlockSerializesConvenienceConstructors(): void
    {
        self::assertSame(
            [
                'LINK' => [
                    'USER_ID' => 1,
                ],
            ],
            LinkBlock::user(1)->build()
        );

        self::assertSame(
            [
                'LINK' => [
                    'CHAT_ID' => 123,
                ],
            ],
            LinkBlock::chat(123)->build()
        );

        self::assertSame(
            [
                'LINK' => [
                    'NETWORK_ID' => 'network-user-example',
                ],
            ],
            LinkBlock::network('network-user-example')->build()
        );
    }

    #[Test]
    public function linkBlockRejectsConflictingTargets(): void
    {
        $linkBlock = LinkBlock::url('https://apidocs.bitrix24.ru');

        $this->expectException(\InvalidArgumentException::class);
        $linkBlock->userId(1);
    }

    #[Test]
    public function linkBlockSerializesAdditionalTargetMetadata(): void
    {
        self::assertSame(
            [
                'LINK' => [
                    'USER_ID' => 1,
                    'NAME' => 'Current user',
                    'DESC' => 'Team directory entry',
                    'HTML' => '<strong>Portal user</strong>',
                    'PREVIEW' => 'https://example.com/preview.png',
                    'WIDTH' => 320,
                    'HEIGHT' => 200,
                ],
            ],
            LinkBlock::user(1, 'Current user')
                ->description('Team directory entry')
                ->html('<strong>Portal user</strong>')
                ->preview('https://example.com/preview.png')
                ->width(320)
                ->height(200)
                ->build()
        );
    }

    #[Test]
    public function linkBlockRejectsEmptyUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        LinkBlock::url('');
    }

    #[Test]
    public function linkBlockRejectsNonPositiveDimensions(): void
    {
        $linkBlock = LinkBlock::url('https://apidocs.bitrix24.ru');

        $this->expectException(\InvalidArgumentException::class);
        $linkBlock->width(0);
    }

    #[Test]
    public function userBlockSerializesOptionalFields(): void
    {
        $userBlock = UserBlock::name('Ivan Ivanov')
            ->userId(1)
            ->chatId(123)
            ->botId(2)
            ->networkId('network-user-example')
            ->avatar('https://example.com/avatar.png')
            ->link('https://example.com/profile')
            ->avatarType(AttachAvatarType::user);

        self::assertSame(
            [
                'USER' => [
                    'NAME' => 'Ivan Ivanov',
                    'USER_ID' => 1,
                    'CHAT_ID' => 123,
                    'BOT_ID' => 2,
                    'NETWORK_ID' => 'network-user-example',
                    'AVATAR' => 'https://example.com/avatar.png',
                    'LINK' => 'https://example.com/profile',
                    'AVATAR_TYPE' => 'USER',
                ],
            ],
            $userBlock->build()
        );
    }

    #[Test]
    public function userBlockSerializesRemainingFluentSetters(): void
    {
        self::assertSame(
            [
                'USER' => [
                    'NAME' => 'Network user',
                    'NETWORK_ID' => 'network-user-example',
                    'AVATAR' => 'https://example.com/avatar.png',
                    'LINK' => 'https://example.com/profile',
                    'AVATAR_TYPE' => 'CHAT',
                ],
            ],
            UserBlock::name('Network user')
                ->networkId('network-user-example')
                ->avatar('https://example.com/avatar.png')
                ->link('https://example.com/profile')
                ->avatarType(AttachAvatarType::chat)
                ->build()
        );
    }

    #[Test]
    public function userBlockSerializesAvatarTypeAliases(): void
    {
        self::assertSame(
            [
                'USER' => [
                    'NAME' => 'Portal user',
                    'USER_ID' => 1,
                    'AVATAR_TYPE' => 'USER',
                ],
            ],
            UserBlock::name('Portal user')->userId(1)->avatarTypeUser()->build()
        );

        self::assertSame(
            [
                'USER' => [
                    'NAME' => 'Payload chat',
                    'CHAT_ID' => 123,
                    'AVATAR_TYPE' => 'CHAT',
                ],
            ],
            UserBlock::name('Payload chat')->chatId(123)->avatarTypeChat()->build()
        );

        self::assertSame(
            [
                'USER' => [
                    'NAME' => 'Payload bot',
                    'BOT_ID' => 2,
                    'AVATAR_TYPE' => 'BOT',
                ],
            ],
            UserBlock::name('Payload bot')->botId(2)->avatarTypeBot()->build()
        );
    }

    #[Test]
    public function userBlockRejectsEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        UserBlock::name('');
    }

    #[Test]
    public function delimiterBlockSerializesEmptyPayloadWhenNoFieldsSet(): void
    {
        self::assertSame(
            ['DELIMITER' => []],
            DelimiterBlock::create()->build()
        );
    }

    #[Test]
    public function delimiterBlockSerializesSizeAndColor(): void
    {
        self::assertSame(
            [
                'DELIMITER' => [
                    'SIZE' => 2,
                    'COLOR' => '#ff0000',
                ],
            ],
            DelimiterBlock::create()
                ->size(2)
                ->color('#ff0000')
                ->build()
        );
    }

    #[Test]
    public function delimiterBlockRejectsNonPositiveSize(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        DelimiterBlock::create()->size(0);
    }

    #[Test]
    public function delimiterBlockRejectsInvalidColor(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        DelimiterBlock::create()->color('29619b');
    }
}
