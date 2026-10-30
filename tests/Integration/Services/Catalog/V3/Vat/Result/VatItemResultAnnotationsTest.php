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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\Vat\Result;

use Bitrix24\SDK\Services\Catalog\V3\CatalogV3ServiceBuilder;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Result\VatItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(VatItemResult::class)]
class VatItemResultAnnotationsTest extends TestCase
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
        $fields = $this->catalog->vatField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), VatItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->catalog->vatField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        // Live REST v3 reports object for this date-time DTO property; OpenAPI specifies string/date-time.
        self::assertContains($fields['timestampX']['type'], ['object', 'string', 'datetime']);
        $fields['timestampX']['type'] = 'datetime';
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, VatItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeCasting(): void
    {
        $vats = $this->catalog->vat()->list()->getVats();
        self::assertNotEmpty($vats, 'test portal must have at least one VAT rate');
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($vats[0], VatItemResult::class);
    }
}
