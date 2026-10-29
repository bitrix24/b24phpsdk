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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\V3\Measure\Service;

use Bitrix24\SDK\Services\Catalog\V3\Measure\Service\MeasureListPagination;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MeasureListPagination::class)]
class MeasureListPaginationTest extends TestCase
{
    #[Test]
    public function testToArrayDropsUnsetValues(): void
    {
        $this->assertSame([], (new MeasureListPagination())->toArray());
        $this->assertSame(['page' => 2], (new MeasureListPagination(page: 2))->toArray());
        $this->assertSame(['page' => 3, 'limit' => 20, 'offset' => 10], (new MeasureListPagination(3, 20, 10))->toArray());
    }
}
