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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\V3\Section\Service;

use Bitrix24\SDK\Services\Catalog\V3\Section\Service\SectionListPagination;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SectionListPagination::class)]
class SectionListPaginationTest extends TestCase
{
    #[Test]
    public function testToArrayDropsUnsetValues(): void
    {
        $this->assertSame([], (new SectionListPagination())->toArray());
        $this->assertSame(['offset' => 40], (new SectionListPagination(offset: 40))->toArray());
        $this->assertSame(['page' => 3, 'limit' => 20, 'offset' => 10], (new SectionListPagination(3, 20, 10))->toArray());
    }
}
