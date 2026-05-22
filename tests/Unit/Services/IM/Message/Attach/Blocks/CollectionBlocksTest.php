<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please see the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Message\Attach\Blocks;

use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\FileBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\ImageBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\GridBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Enums\AttachColorToken;
use Bitrix24\SDK\Services\IM\Message\Attach\Enums\GridDisplay;
use Bitrix24\SDK\Services\IM\Message\Attach\Items\FileItem;
use Bitrix24\SDK\Services\IM\Message\Attach\Items\ImageItem;
use Bitrix24\SDK\Services\IM\Message\Attach\Items\GridItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageBlock::class)]
#[CoversClass(FileBlock::class)]
#[CoversClass(GridBlock::class)]
#[CoversClass(ImageItem::class)]
#[CoversClass(FileItem::class)]
#[CoversClass(GridItem::class)]
final class CollectionBlocksTest extends TestCase
{
    #[Test]
    public function imageBlockSerializesMultipleItems(): void
    {
        self::assertSame(
            [
                'IMAGE' => [
                    [
                        'LINK' => 'https://example.com/1.png',
                        'NAME' => 'One',
                    ],
                    [
                        'LINK' => 'https://example.com/2.png',
                        'NAME' => 'Two',
                        'PREVIEW' => 'https://example.com/2-preview.png',
                        'WIDTH' => 640,
                        'HEIGHT' => 480,
                    ],
                ],
            ],
            ImageBlock::create()
                ->item(ImageItem::link('https://example.com/1.png')->name('One'))
                ->item(
                    ImageItem::link('https://example.com/2.png')
                        ->name('Two')
                        ->preview('https://example.com/2-preview.png')
                        ->width(640)
                        ->height(480)
                )
                ->build()
        );
    }

    #[Test]
    public function fileBlockSerializesOptionalFields(): void
    {
        self::assertSame(
            [
                'FILE' => [
                    [
                        'LINK' => 'https://example.com/mantis.jpg',
                        'NAME' => 'mantis.jpg',
                        'SIZE' => 1500000,
                    ],
                ],
            ],
            FileBlock::create()
                ->item(
                    FileItem::link('https://example.com/mantis.jpg')
                        ->name('mantis.jpg')
                        ->size(1500000)
                )
                ->build()
        );
    }

    #[Test]
    public function gridBlockSerializesDisplayAndItemMetadata(): void
    {
        self::assertSame(
            [
                'GRID' => [
                    [
                        'NAME' => 'Project',
                        'VALUE' => 'BUGS',
                        'DISPLAY' => 'ROW',
                        'WIDTH' => 250,
                        'COLOR_TOKEN' => 'alert',
                        'COLOR' => '#ff0000',
                    ],
                    [
                        'NAME' => 'Priority',
                        'VALUE' => 'High',
                        'DISPLAY' => 'ROW',
                        'WIDTH' => 320,
                        'COLOR_TOKEN' => 'primary',
                        'COLOR' => '#00ff00',
                    ],
                    [
                        'NAME' => 'Owner',
                        'DISPLAY' => 'ROW',
                        'USER_ID' => 42,
                        'CHAT_ID' => 77,
                        'WIDTH' => 250,
                        'COLOR_TOKEN' => 'alert',
                        'COLOR' => '#ff0000',
                    ],
                ],
            ],
            GridBlock::display(GridDisplay::row)
                ->width(250)
                ->colorToken(AttachColorToken::alert)
                ->color('#ff0000')
                ->item(
                    GridItem::name('Project')
                        ->value('BUGS')
                )
                ->item(
                    GridItem::name('Priority')
                        ->value('High')
                        ->width(320)
                        ->colorToken(AttachColorToken::primary)
                        ->color('#00ff00')
                )
                ->item(
                    GridItem::name('Owner')
                        ->userId(42)
                        ->chatId(77)
                )
                ->build()
        );
    }

    #[Test]
    public function imageBlockRejectsEmptyCollections(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ImageBlock::create()->build();
    }

    #[Test]
    public function fileBlockRejectsEmptyCollections(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        FileBlock::create()->build();
    }

    #[Test]
    public function gridBlockRejectsEmptyCollections(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        GridBlock::display(GridDisplay::row)->build();
    }

    #[Test]
    public function imageItemRejectsEmptyLink(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ImageItem::link('');
    }

    #[Test]
    public function fileItemRejectsNonPositiveSize(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        FileItem::link('https://example.com/mantis.jpg')->size(0);
    }

    #[Test]
    public function gridItemRejectsInvalidColor(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        GridItem::name('Priority')->color('ff0000');
    }
}
