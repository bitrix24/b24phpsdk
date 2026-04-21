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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Message\Attach;

use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\DelimiterBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\LinkBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Attach;
use Bitrix24\SDK\Services\IM\Message\Attach\Enums\AttachColorToken;
use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\UserBlock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Attach::class)]
final class AttachTest extends TestCase
{
    #[Test]
    public function buildReturnsShortAttachFormWhenNoMetaFieldsPresent(): void
    {
        $attach = Attach::create()->message('Hello');

        self::assertSame(
            [
                ['MESSAGE' => 'Hello'],
            ],
            $attach->build()
        );
    }

    #[Test]
    public function buildReturnsFullAttachFormWhenMetaFieldsPresent(): void
    {
        $attach = Attach::create()
            ->id(1)
            ->colorToken(AttachColorToken::primary)
            ->message('Hello');

        self::assertSame(
            [
                'ID' => 1,
                'COLOR_TOKEN' => 'primary',
                'BLOCKS' => [
                    ['MESSAGE' => 'Hello'],
                ],
            ],
            $attach->build()
        );
    }

    #[Test]
    public function buildReturnsFullAttachFormWhenColorPresent(): void
    {
        $attach = Attach::create()
            ->color('#29619b')
            ->message('Hello')
            ->delimiter();

        self::assertSame(
            [
                'COLOR' => '#29619b',
                'BLOCKS' => [
                    ['MESSAGE' => 'Hello'],
                    ['DELIMITER' => []],
                ],
            ],
            $attach->build()
        );
    }

    #[Test]
    public function buildPreservesBlockOrderInShortForm(): void
    {
        $attach = Attach::create()
            ->message('First')
            ->delimiter()
            ->message('Second');

        self::assertSame(
            [
                ['MESSAGE' => 'First'],
                ['DELIMITER' => []],
                ['MESSAGE' => 'Second'],
            ],
            $attach->build()
        );
    }

    #[Test]
    public function idRejectsNonPositiveValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Attach::create()->id(0);
    }

    #[Test]
    public function colorRejectsInvalidHexValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Attach::create()->color('29619b');
    }

    #[Test]
    public function delimiterSerializesDefaultBlock(): void
    {
        self::assertSame(
            ['DELIMITER' => []],
            DelimiterBlock::create()->build()
        );
    }

    #[Test]
    public function buildAcceptsScalarBlocksAddedDirectly(): void
    {
        $attach = Attach::create()
            ->add(LinkBlock::url('https://apidocs.bitrix24.ru')->name('Docs'))
            ->add(UserBlock::name('Current user')->avatarTypeChat());

        self::assertSame(
            [
                [
                    'LINK' => [
                        'LINK' => 'https://apidocs.bitrix24.ru',
                        'NAME' => 'Docs',
                    ],
                ],
                [
                    'USER' => [
                        'NAME' => 'Current user',
                        'AVATAR_TYPE' => 'CHAT',
                    ],
                ],
            ],
            $attach->build()
        );
    }
}
