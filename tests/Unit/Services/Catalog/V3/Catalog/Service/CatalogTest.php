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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\V3\Catalog\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Service\CatalogFilter;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Service\CatalogItemBuilder;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Service\CatalogListPagination;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Service\CatalogSelectBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(Catalog::class)]
class CatalogTest extends TestCase
{
    private const array OFFERS_CATALOG = [
        'id' => 26,
        'iblockId' => 26,
        'yandexExport' => 'N',
        'subscription' => 'Y',
        'vatId' => 2,
        'productIblockId' => 24,
        'skuPropertyId' => 102,
        'iblockTypeId' => 'CRM_PRODUCT_CATALOG',
        'lid' => 's1',
        'name' => 'CRM catalog (offers)',
    ];

    #[Test]
    public function testGetSendsIdAndSelectAndMapsItem(): void
    {
        $catalog = $this->service('catalog.catalog.get', ['id' => 26, 'select' => ['id', 'productIblockId', 'subscription']], ['item' => self::OFFERS_CATALOG])
            ->get(26, (new CatalogSelectBuilder())->productIblockId()->subscription())
            ->catalog();

        $this->assertSame(26, $catalog->id);
        $this->assertSame(26, $catalog->iblockId);
        $this->assertFalse($catalog->yandexExport);
        $this->assertTrue($catalog->subscription);
        $this->assertSame(2, $catalog->vatId);
        $this->assertSame(24, $catalog->productIblockId);
        $this->assertSame(102, $catalog->skuPropertyId);
        $this->assertSame('CRM_PRODUCT_CATALOG', $catalog->iblockTypeId);
        $this->assertSame('s1', $catalog->lid);
        $this->assertSame('CRM catalog (offers)', $catalog->name);
    }

    #[Test]
    public function testGetOmitsEmptySelect(): void
    {
        $this->service('catalog.catalog.get', ['id' => 26], ['item' => self::OFFERS_CATALOG])->get(26);
    }

    #[Test]
    public function testListSendsFilterSelectOrderAndPagination(): void
    {
        $filter = new CatalogFilter();
        $filter->productIblockId()->eq(0);
        $filter->subscription()->eq(true);

        $catalogs = $this->service(
            'catalog.catalog.list',
            [
                'filter' => [['productIblockId', '=', 0], ['subscription', '=', 'Y']],
                'select' => ['id', 'name'],
                'order' => ['iblockId' => 'ASC'],
                'pagination' => ['limit' => 10],
            ],
            ['items' => [self::OFFERS_CATALOG]]
        )->list($filter, (new CatalogSelectBuilder())->name(), ['iblockId' => 'ASC'], new CatalogListPagination(limit: 10))->getCatalogs();

        $this->assertCount(1, $catalogs);
        $this->assertSame(26, $catalogs[0]->id);
    }

    #[Test]
    public function testListWithoutArgumentsSendsEmptyPayload(): void
    {
        $this->assertSame([], $this->service('catalog.catalog.list', [], ['items' => []])->list()->getCatalogs());
    }

    #[Test]
    public function testAddSendsFieldsWithYesNoFlagsAndReturnsCreatedItem(): void
    {
        $catalog = $this->service(
            'catalog.catalog.add',
            ['fields' => ['iblockId' => 132, 'subscription' => 'N', 'yandexExport' => 'Y', 'vatId' => 2]],
            ['item' => ['id' => 132, 'iblockId' => 132, 'subscription' => 'N', 'yandexExport' => 'Y', 'vatId' => 2]]
        )->add((new CatalogItemBuilder())->iblockId(132)->subscription(false)->yandexExport(true)->vatId(2))->catalog();

        $this->assertSame(132, $catalog->id);
        $this->assertTrue($catalog->yandexExport);
        $this->assertFalse($catalog->subscription);
    }

    #[Test]
    public function testUpdateSendsIdAndFieldsAndReturnsUpdatedItem(): void
    {
        $catalog = $this->service(
            'catalog.catalog.update',
            ['id' => 132, 'fields' => ['subscription' => 'Y']],
            ['item' => ['id' => 132, 'subscription' => 'Y']]
        )->update(132, (new CatalogItemBuilder())->subscription(true))->catalog();

        $this->assertTrue($catalog->subscription);
    }

    #[Test]
    public function testDeleteSendsIdAndReadsNestedResult(): void
    {
        $this->assertTrue($this->service('catalog.catalog.delete', ['id' => 132], ['result' => true])->delete(132)->isSuccess());
    }

    #[Test]
    #[DataProvider('isOffersCases')]
    public function testIsOffersCallsLowercaseMethodAndReadsFlag(bool $isOffers): void
    {
        $this->assertSame(
            $isOffers,
            $this->service('catalog.catalog.isoffers', ['id' => 26], ['result' => $isOffers])->isOffers(26)->isOffers()
        );
    }

    public static function isOffersCases(): \Generator
    {
        yield 'offers catalog' => [true];
        yield 'product catalog' => [false];
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $metadata = (new \ReflectionClass(Catalog::class))->getAttributes(ApiServiceMetadata::class)[0]->newInstance();
        $this->assertSame(['catalog'], $metadata->scope->getScopeCodes());
        $base = 'https://apidocs.bitrix24.com/api-reference/catalog/catalog/';
        $expected = [
            'get' => ['catalog.catalog.get', $base . 'catalog-catalog-get-rest-v3.html'],
            'list' => ['catalog.catalog.list', $base . 'catalog-catalog-list-rest-v3.html'],
            'add' => ['catalog.catalog.add', $base . 'catalog-catalog-add.html'],
            'update' => ['catalog.catalog.update', $base . 'catalog-catalog-update.html'],
            'delete' => ['catalog.catalog.delete', $base . 'catalog-catalog-delete.html'],
            'isOffers' => ['catalog.catalog.isoffers', $base . 'catalog-catalog-is-offers-rest-v3.html'],
        ];
        foreach ($expected as $method => [$name, $url]) {
            $endpoint = (new \ReflectionMethod(Catalog::class, $method))->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();
            $this->assertSame($name, $endpoint->name);
            $this->assertSame($url, $endpoint->documentationUrl);
            $this->assertSame(ApiVersion::v3, $endpoint->apiVersion);
        }
    }

    private function service(string $method, array $parameters, array $result): Catalog
    {
        $response = new Response(
            (new MockHttpClient(new JsonMockResponse(['result' => $result])))->request('POST', 'https://example.test'),
            new Command($method, $parameters),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($method, $parameters, ApiVersion::v3)->willReturn($response);

        return new Catalog($core, new NullLogger());
    }
}
