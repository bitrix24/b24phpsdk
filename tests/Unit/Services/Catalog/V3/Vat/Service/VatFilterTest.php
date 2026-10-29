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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\V3\Vat\Service;

use Bitrix24\SDK\Services\Catalog\V3\Vat\Service\VatFilter;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Service\VatItemBuilder;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(VatFilter::class)]
#[CoversClass(VatItemBuilder::class)]
class VatFilterTest extends TestCase
{
    #[Test]
    public function testFilterBuildsTypedConditions(): void
    {
        $filter = new VatFilter();
        $filter->id()->in([2, 4]);
        $filter->active()->eq(true);
        $filter->sort()->gte(100);
        $filter->name()->eq('VAT 20');
        $filter->timestampX()->gt(new CarbonImmutable('2024-01-01T00:00:00+00:00'));

        $conditions = $filter->toArray();

        $this->assertSame(['id', 'in', [2, 4]], $conditions[0]);
        $this->assertSame(['active', '=', true], $conditions[1]);
        $this->assertSame(['sort', '>=', 100], $conditions[2]);
        $this->assertSame(['name', '=', 'VAT 20'], $conditions[3]);
        $this->assertSame('timestampX', $conditions[4][0]);
        $this->assertSame('>', $conditions[4][1]);
    }

    #[Test]
    public function testItemBuilderSupportsRequiredRateField(): void
    {
        $this->assertSame(
            ['name' => 'VAT 10', 'rate' => 10.0, 'active' => false],
            (new VatItemBuilder())->name('VAT 10')->rate(10.0)->active(false)->build()
        );
    }
}
