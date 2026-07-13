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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Extra\Result;

use Bitrix24\SDK\Services\Catalog\Extra\Result\ExtraItemResult;
use Bitrix24\SDK\Services\Catalog\Extra\Service\Extra;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExtraItemResult::class)]
class ExtraItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Extra $extraService;

    #[\Override]
    protected function setUp(): void
    {
        $this->extraService = Fabric::getServiceBuilder()->getCatalogScope()->extra();
    }

    #[Test]
    #[TestDox('all fields in ExtraItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = array_keys($this->extraService->fields()->getFieldsDescription()['extra']);

        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, ExtraItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in ExtraItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->extraService->fields()->getFieldsDescription()['extra'];

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, ExtraItemResult::class);
    }
}
