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

namespace Bitrix24\SDK\Tests\Unit\Services\Note\Document\Service;

use Bitrix24\SDK\Services\Note\Document\Service\DocumentListPagination;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentListPagination::class)]
class DocumentListPaginationTest extends TestCase
{
    #[Test]
    public function testDefaultPageSizeOmitsOnlyNullCursor(): void
    {
        $this->assertSame(['limit' => 50], (new DocumentListPagination())->toArray());
        $this->assertSame(['limit' => 200], (new DocumentListPagination(200))->toArray());
        $this->assertSame(['limit' => 1, 'afterCursor' => []], (new DocumentListPagination(1, []))->toArray());
    }

    #[Test]
    public function testCursorRemainsOpaque(): void
    {
        $cursor = ['stream' => 'collections', 'collectionId' => 123, 'id' => 42, 'future' => ['flag' => false]];

        $this->assertSame(['limit' => 2, 'afterCursor' => $cursor], (new DocumentListPagination(2, $cursor))->toArray());
    }
}
