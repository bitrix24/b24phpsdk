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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Product\Sku\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Product\Sku\Result\SkuItemResult;
use Bitrix24\SDK\Services\Catalog\Product\Sku\Service\Sku;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(SkuItemResult::class)]
class SkuItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private const FULL_SELECT = [
        'id', 'iblockId', 'name', 'active', 'available', 'bundle', 'canBuyZero', 'code',
        'createdBy', 'dateActiveFrom', 'dateActiveTo', 'dateCreate', 'detailPicture', 'detailText',
        'detailTextType', 'height', 'iblockSection', 'iblockSectionId', 'length', 'measure',
        'modifiedBy', 'previewPicture', 'previewText', 'previewTextType', 'purchasingCurrency',
        'purchasingPrice', 'quantity', 'sort', 'subscribe', 'timestampX', 'type', 'vatId',
        'vatIncluded', 'weight', 'width', 'xmlId',
    ];

    private Sku $skuService;

    private int $skuId;

    private int $iblockId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->skuService = $serviceBuilder->getCatalogScope()->productSku();
        $catalogService = $serviceBuilder->getCatalogScope()->catalog();
        $this->iblockId = $catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $this->skuId = $this->skuService->add([
            'iblockId' => $this->iblockId,
            'name' => sprintf('test sku annotations %s', time()),
        ])->sku()->id;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        $this->skuService->delete($this->skuId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in SkuItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->skuService->list(
            self::FULL_SELECT,
            ['id' => $this->skuId, 'iblockId' => $this->iblockId]
        )->getCoreResponse()->getResponseData()->getResult()['units'][0];

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            SkuItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in SkuItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $fields = $this->skuService->fieldsByFilter($this->iblockId)->getFieldsDescription();
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $fields,
            SkuItemResult::class
        );
    }
}
