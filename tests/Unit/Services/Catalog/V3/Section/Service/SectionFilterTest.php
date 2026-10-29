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

use Bitrix24\SDK\Services\Catalog\V3\Section\Service\SectionFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SectionFilter::class)]
class SectionFilterTest extends TestCase
{
    #[Test]
    public function testFilterBuildsTypedConditionsWithYesNoActive(): void
    {
        $filter = new SectionFilter();
        $filter->id()->in([8, 10]);
        $filter->iblockSectionId()->eq(0);
        $filter->sort()->lte(500);
        $filter->name()->eq('Root');
        $filter->code()->eq('root');
        $filter->xmlId()->eq('x-1');
        $filter->active()->eq(false);

        $this->assertSame(
            [
                ['id', 'in', [8, 10]],
                ['iblockSectionId', '=', 0],
                ['sort', '<=', 500],
                ['name', '=', 'Root'],
                ['code', '=', 'root'],
                ['xmlId', '=', 'x-1'],
                ['active', '=', 'N'],
            ],
            $filter->toArray()
        );
    }
}
