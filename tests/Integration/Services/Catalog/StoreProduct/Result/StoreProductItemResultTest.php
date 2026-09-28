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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\StoreProduct\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\StoreProduct\Result\StoreProductItemResult;
use Bitrix24\SDK\Services\Catalog\StoreProduct\Service\StoreProduct;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(StoreProductItemResult::class)]
class StoreProductItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private StoreProduct $storeProductService;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in StoreProductItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->storeProductService->list()->getCoreResponse()
            ->getResponseData()->getResult()['storeProducts'][0];

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            StoreProductItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in StoreProductItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $item = $this->storeProductService->list()->getStoreProducts()[0];
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $item,
            StoreProductItemResult::class
        );
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->storeProductService = Factory::getServiceBuilder()->getCatalogScope()->storeProduct();
    }
}
