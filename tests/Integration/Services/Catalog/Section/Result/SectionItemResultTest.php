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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Section\Result;

use Bitrix24\SDK\Services\Catalog\Section\Result\SectionItemResult;
use Bitrix24\SDK\Services\Catalog\Section\Service\Section;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(SectionItemResult::class)]
class SectionItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Section $sectionService;

    private int $sectionId;

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder(true);
        $this->sectionService = $serviceBuilder->getCatalogScope()->section();
        $iblockId = $serviceBuilder->getCatalogScope()->catalog()
            ->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $this->sectionId = $this->sectionService->add([
            'name' => sprintf('test section annotations %s', time()),
            'iblockId' => $iblockId,
        ])->section()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->sectionService->delete($this->sectionId);
    }

    #[Test]
    #[TestDox('all fields in SectionItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->sectionService->get($this->sectionId)
            ->getCoreResponse()->getResponseData()->getResult()['section'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), SectionItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in SectionItemResult have valid type annotation in phpdoc')]
    public function testAllFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->sectionService->getFields()->getFieldsDescription();
        foreach ($allFields as $field => $params) {
            $newParams = [];
            foreach ($params as $key => $value) {
                $newParams[mb_strtolower((string)$key)] = $value;
            }

            $allFields[$field] = $newParams;
        }

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($allFields, SectionItemResult::class);
    }
}
