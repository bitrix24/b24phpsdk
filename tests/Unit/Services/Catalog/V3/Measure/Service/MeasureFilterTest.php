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

use Bitrix24\SDK\Services\Catalog\V3\Measure\Service\MeasureFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MeasureFilter::class)]
class MeasureFilterTest extends TestCase
{
    #[Test]
    public function testFilterBuildsTypedConditionsWithNativeBoolean(): void
    {
        $filter = new MeasureFilter();
        $filter->id()->in([1, 3]);
        $filter->code()->eq(6);
        $filter->measureTitle()->eq('Metre');
        $filter->symbol()->eq('m');
        $filter->symbolIntl()->eq('m');
        $filter->symbolLetterIntl()->eq('MTR');
        $filter->isDefault()->eq(false);

        $this->assertSame(
            [
                ['id', 'in', [1, 3]],
                ['code', '=', 6],
                ['measureTitle', '=', 'Metre'],
                ['symbol', '=', 'm'],
                ['symbolIntl', '=', 'm'],
                ['symbolLetterIntl', '=', 'MTR'],
                ['isDefault', '=', false],
            ],
            $filter->toArray()
        );
    }
}
