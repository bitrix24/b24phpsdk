<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Disk\File\Service;

use Bitrix24\SDK\Services\Disk\File\Service\File;
use Bitrix24\SDK\Tests\Integration\Services\Disk\TemporaryDiskFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(File::class)]
final class FileSearchTest extends TestCase
{
    public function testSearchFindsScopedFileAndFolderAndHandlesEmptyResults(): void
    {
        $fixture = TemporaryDiskFixture::create();
        try {
            $file = new File($fixture->core, new NullLogger());
            $mixed = $fixture->waitForSearch($file)->items();
            self::assertCount(2, $mixed);
            self::assertEqualsCanonicalizing(['file', 'folder'], array_map(static fn($item): string => $item->TYPE, $mixed));
            $filter = ['STORAGE_ID' => $fixture->storageId, 'FOLDER_ID' => $fixture->rootId];
            $files = $file->search($fixture->query, filter: $filter)->items();
            self::assertCount(1, $files);
            self::assertSame($fixture->fileId, $files[0]->ID);
            $folders = $file->search($fixture->query, 'folder', $filter)->items();
            self::assertCount(1, $folders);
            self::assertSame($fixture->folderId, $folders[0]->ID);
            self::assertSame([], $file->search($fixture->query . 'missing', 'all', $filter)->items());
            self::assertCount(1, $file->search($fixture->query, 'all', $filter, 1)->items());
        } finally {
            $fixture->delete();
        }
    }
}
