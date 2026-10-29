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

namespace Bitrix24\SDK\Tests\Unit\Services\Note\File\Service;

use Bitrix24\SDK\Services\Note\File\Service\FileListPagination;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileListPagination::class)]
class FileListPaginationTest extends TestCase
{
    #[Test]
    public function testDefaultPageSizeOmitsOnlyNullCursor(): void
    {
        $this->assertSame(['limit' => 50], (new FileListPagination())->toArray());
        $this->assertSame(['limit' => 17], (new FileListPagination(17))->toArray());
        $this->assertSame(['limit' => 0, 'afterCursor' => []], (new FileListPagination(0, []))->toArray());
    }

    #[Test]
    public function testCursorRemainsOpaque(): void
    {
        $cursor = ['id' => '9223372036854775808', 'position' => '0007', 'token' => null, 'future' => ['flag' => false, 'ratio' => 1.25]];

        $this->assertSame(['limit' => 11, 'afterCursor' => $cursor], (new FileListPagination(11, $cursor))->toArray());
    }
}
