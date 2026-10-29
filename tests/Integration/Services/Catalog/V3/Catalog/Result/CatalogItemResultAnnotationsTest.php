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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\Catalog\Result;

use Bitrix24\SDK\Services\Catalog\V3\Catalog\Result\CatalogItemResult;
use Bitrix24\SDK\Services\Catalog\V3\CatalogV3ServiceBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CatalogItemResult::class)]
class CatalogItemResultAnnotationsTest extends TestCase
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
        $fields = $this->catalog->catalogField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), CatalogItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->catalog->catalogField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        // Live REST v3 reports the Y/N flags as string; the SDK exposes them as bool.
        foreach (['yandexExport', 'subscription'] as $flag) {
            self::assertSame('string', $fields[$flag]['type']);
            $fields[$flag]['type'] = 'bool';
        }

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, CatalogItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeCasting(): void
    {
        $catalogs = $this->catalog->catalog()->list()->getCatalogs();
        self::assertNotEmpty($catalogs, 'test portal must have a trade catalog');
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($catalogs[0], CatalogItemResult::class);
    }
}
