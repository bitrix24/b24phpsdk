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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\Section\Result;

use Bitrix24\SDK\Services\Catalog\V3\CatalogV3ServiceBuilder;
use Bitrix24\SDK\Services\Catalog\V3\Section\Result\SectionItemResult;
use Bitrix24\SDK\Services\Catalog\V3\Section\Service\SectionItemBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\ProductCatalogIblockTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SectionItemResult::class)]
class SectionItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;
    use ProductCatalogIblockTrait;

    private CatalogV3ServiceBuilder $catalog;

    #[\Override]
    protected function setUp(): void
    {
        $this->catalog = Factory::getServiceBuilder()->getCatalogScope()->v3();
    }

    #[Test]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->catalog->sectionField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), SectionItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->catalog->sectionField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        // Live REST v3 reports the Y/N flag `active` as string; the SDK exposes it as bool.
        self::assertSame('string', $fields['active']['type']);
        $fields['active']['type'] = 'bool';
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, SectionItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeCasting(): void
    {
        $section = $this->catalog->section()->add(
            (new SectionItemBuilder())->iblockId($this->getProductCatalogIblockId())->name('sdk v3 annotations section')
        )->section();

        try {
            $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($section, SectionItemResult::class);
        } finally {
            $this->catalog->section()->delete($section->id);
        }
    }
}
