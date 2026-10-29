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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\CatalogField\Result;

use Bitrix24\SDK\Services\Catalog\V3\CatalogField\Result\CatalogFieldItemResult;
use Bitrix24\SDK\Services\Catalog\V3\CatalogField\Service\CatalogField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(CatalogFieldItemResult::class)]
class CatalogFieldItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private CatalogField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getCatalogScope()->v3()->catalogField();
    }

    #[Test]
    #[TestDox('all fields in CatalogFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->service->get('iblockId')->getCoreResponse()->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), CatalogFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in CatalogFieldItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $this->service->get('iblockId')->field(),
            CatalogFieldItemResult::class
        );
    }
}
