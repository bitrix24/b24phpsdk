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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\DocumentElement\Result;

use Bitrix24\SDK\Core\Fields\FieldsFilter;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Result\DocumentElementItemResult;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Service\DocumentElement;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentElementItemResult::class)]
class DocumentElementItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private DocumentElement $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Fabric::getServiceBuilder()->getCatalogScope()->documentElement();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $this->assertBitrix24AllResultItemFieldsAnnotated(
            (new FieldsFilter())->filterSystemFields(array_keys($this->service->getFields()->getFieldsDescription())),
            DocumentElementItemResult::class
        );
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->service->getFields()->getFieldsDescription(),
            DocumentElementItemResult::class
        );
    }
}
