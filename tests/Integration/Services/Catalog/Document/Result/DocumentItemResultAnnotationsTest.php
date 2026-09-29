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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Document\Result;

use Bitrix24\SDK\Core\Fields\FieldsFilter;
use Bitrix24\SDK\Services\Catalog\Document\Result\DocumentItemResult;
use Bitrix24\SDK\Services\Catalog\Document\Service\Document;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentItemResult::class)]
class DocumentItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Document $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Fabric::getServiceBuilder()->getCatalogScope()->document();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $this->assertBitrix24AllResultItemFieldsAnnotated(
            (new FieldsFilter())->filterSystemFields(array_keys($this->service->getFields()->getFieldsDescription())),
            DocumentItemResult::class
        );
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->service->getFields()->getFieldsDescription(),
            DocumentItemResult::class
        );
    }
}
