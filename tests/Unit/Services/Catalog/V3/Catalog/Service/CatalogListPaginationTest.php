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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\V3\Catalog\Service;

use Bitrix24\SDK\Services\Catalog\V3\Catalog\Service\CatalogListPagination;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CatalogListPagination::class)]
class CatalogListPaginationTest extends TestCase
{
    #[Test]
    public function testToArrayDropsUnsetValues(): void
    {
        $this->assertSame([], (new CatalogListPagination())->toArray());
        $this->assertSame(['limit' => 1], (new CatalogListPagination(limit: 1))->toArray());
        $this->assertSame(['page' => 3, 'limit' => 20, 'offset' => 10], (new CatalogListPagination(3, 20, 10))->toArray());
    }
}
