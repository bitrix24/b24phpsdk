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

use Bitrix24\SDK\Services\Catalog\V3\Catalog\Service\CatalogFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CatalogFilter::class)]
class CatalogFilterTest extends TestCase
{
    #[Test]
    public function testFilterBuildsTypedConditionsWithYesNoFlags(): void
    {
        $filter = new CatalogFilter();
        $filter->id()->in([24, 26]);
        $filter->iblockId()->eq(24);
        $filter->vatId()->eq(2);
        $filter->productIblockId()->gt(0);
        $filter->skuPropertyId()->eq(102);
        $filter->iblockTypeId()->eq('CRM_PRODUCT_CATALOG');
        $filter->lid()->eq('s1');
        $filter->name()->eq('CRM');
        $filter->yandexExport()->eq(false);
        $filter->subscription()->eq(true);

        $this->assertSame(
            [
                ['id', 'in', [24, 26]],
                ['iblockId', '=', 24],
                ['vatId', '=', 2],
                ['productIblockId', '>', 0],
                ['skuPropertyId', '=', 102],
                ['iblockTypeId', '=', 'CRM_PRODUCT_CATALOG'],
                ['lid', '=', 's1'],
                ['name', '=', 'CRM'],
                ['yandexExport', '=', 'N'],
                ['subscription', '=', 'Y'],
            ],
            $filter->toArray()
        );
    }
}
