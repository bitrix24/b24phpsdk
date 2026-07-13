<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Measure\Result;

use Bitrix24\SDK\Services\Catalog\Measure\Result\MeasureItemResult;
use Bitrix24\SDK\Services\Catalog\Measure\Service\Measure;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(MeasureItemResult::class)]
class MeasureItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Measure $measureService;

    #[\Override]
    protected function setUp(): void
    {
        $this->measureService = Fabric::getServiceBuilder()->getCatalogScope()->measure();
    }

    #[Test]
    #[TestDox('all fields in MeasureItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = array_keys($this->measureService->fields()->getFieldsDescription());

        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, MeasureItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in MeasureItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->measureService->fields()->getFieldsDescription();

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, MeasureItemResult::class);
    }
}
