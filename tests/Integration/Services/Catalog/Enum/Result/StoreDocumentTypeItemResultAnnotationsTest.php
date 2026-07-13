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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Enum\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Enum\Result\StoreDocumentTypeItemResult;
use Bitrix24\SDK\Services\Catalog\Enum\Service\CatalogEnum;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(StoreDocumentTypeItemResult::class)]
class StoreDocumentTypeItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private CatalogEnum $catalogEnumService;

    #[\Override]
    protected function setUp(): void
    {
        $this->catalogEnumService = Fabric::getServiceBuilder()->getCatalogScope()->catalogEnum();
    }

    /**
     * @return array<string, mixed>
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstStoreDocumentTypeRawItem(): array
    {
        $rawItems = $this->catalogEnumService->getStoreDocumentTypes()
            ->getCoreResponse()->getResponseData()->getResult()['enum'];

        self::assertNotEmpty($rawItems, 'getStoreDocumentTypes() must return at least one item to run this test');

        return $rawItems[0];
    }

    #[Test]
    #[TestDox('all fields in StoreDocumentTypeItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->getFirstStoreDocumentTypeRawItem();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            StoreDocumentTypeItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in StoreDocumentTypeItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $rawItem = $this->getFirstStoreDocumentTypeRawItem();
        $storeDocumentTypeItemResult = new StoreDocumentTypeItemResult($rawItem);

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $storeDocumentTypeItemResult,
            StoreDocumentTypeItemResult::class
        );
    }
}
