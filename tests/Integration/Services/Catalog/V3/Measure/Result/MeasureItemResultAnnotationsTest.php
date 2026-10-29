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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\Measure\Result;

use Bitrix24\SDK\Services\Catalog\V3\CatalogV3ServiceBuilder;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Result\MeasureItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MeasureItemResult::class)]
class MeasureItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private CatalogV3ServiceBuilder $catalog;

    #[\Override]
    protected function setUp(): void
    {
        $this->catalog = Factory::getServiceBuilder()->getCatalogScope()->v3();
    }

    #[Test]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->catalog->measureField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), MeasureItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->catalog->measureField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, MeasureItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeCasting(): void
    {
        $measures = $this->catalog->measure()->list()->getMeasures();
        self::assertNotEmpty($measures, 'test portal must have at least one unit of measure');
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($measures[0], MeasureItemResult::class);
    }
}
