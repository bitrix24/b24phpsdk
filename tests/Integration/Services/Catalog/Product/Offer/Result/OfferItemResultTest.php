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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Product\Offer\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Product\Offer\Result\OfferItemResult;
use Bitrix24\SDK\Services\Catalog\Product\Offer\Service\Offer;
use Bitrix24\SDK\Services\Catalog\Product\Sku\Service\Sku;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(OfferItemResult::class)]
class OfferItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Offer $offerService;

    private Sku $skuService;

    private int $skuId;

    private int $offerId;

    private int $offersCatalogIblockId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->offerService = $serviceBuilder->getCatalogScope()->productOffer();
        $this->skuService = $serviceBuilder->getCatalogScope()->productSku();
        $catalogService = $serviceBuilder->getCatalogScope()->catalog();

        $productCatalog = null;
        $offersCatalog = null;
        foreach ($catalogService->list([], [], [], 1)->getCatalogs() as $catalogItemResult) {
            if ($catalogItemResult->productIblockId === null) {
                $productCatalog = $catalogItemResult;
            } else {
                $offersCatalog = $catalogItemResult;
            }
        }

        $this->offersCatalogIblockId = $offersCatalog->iblockId;

        $this->skuId = $this->skuService->add([
            'iblockId' => $productCatalog->iblockId,
            'name' => sprintf('test sku for offer annotations %s', time()),
        ])->sku()->id;

        $this->offerId = $this->offerService->add([
            'iblockId' => $offersCatalog->iblockId,
            'name' => sprintf('test offer annotations %s', time()),
            'parentId' => $this->skuId,
        ])->offer()->id;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        $this->offerService->delete($this->offerId);
        $this->skuService->delete($this->skuId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in OfferItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->offerService->get($this->offerId)->getCoreResponse()
            ->getResponseData()->getResult()['offer'];

        // dynamic catalog properties (propertyN) vary per portal and are intentionally not annotated
        $fieldCodes = array_filter(
            array_keys($rawItem),
            static fn (string $fieldCode): bool => in_array(preg_match('/^property\d+$/', $fieldCode), [0, false], true)
        );

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $fieldCodes,
            OfferItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in OfferItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        // priceType and negativeAmountTrace are present only in fieldsByFilter response, not in item response, and are not annotated
        // dynamic catalog properties (propertyN) vary per portal and are intentionally not annotated
        $fieldsNotInItemResponse = ['priceType', 'negativeAmountTrace'];
        $fields = array_filter(
            $this->offerService->fieldsByFilter($this->offersCatalogIblockId)->getFieldsDescription()['offer'],
            static fn (string $fieldCode): bool => !in_array($fieldCode, $fieldsNotInItemResponse, true) && in_array(preg_match('/^property\d+$/', $fieldCode), [0, false], true),
            ARRAY_FILTER_USE_KEY
        );
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $fields,
            OfferItemResult::class
        );
    }
}
