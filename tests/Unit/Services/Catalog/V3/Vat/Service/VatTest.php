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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\V3\Vat\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Service\Vat;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Service\VatFilter;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Service\VatItemBuilder;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Service\VatListPagination;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Service\VatSelectBuilder;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(Vat::class)]
class VatTest extends TestCase
{
    private const array VAT_ITEM = [
        'id' => 4,
        'timestampX' => '2024-12-15T09:19:27+03:00',
        'active' => true,
        'sort' => 200,
        'name' => 'VAT 20',
        'rate' => 20,
    ];

    #[Test]
    public function testGetSendsIdAndSelectAndMapsItem(): void
    {
        $vat = $this->service('catalog.vat.get', ['id' => 4, 'select' => ['id', 'name', 'rate']], ['item' => self::VAT_ITEM])
            ->get(4, (new VatSelectBuilder())->name()->rate())
            ->vat();

        $this->assertSame(4, $vat->id);
        $this->assertSame('VAT 20', $vat->name);
        $this->assertSame(20.0, $vat->rate);
        $this->assertTrue($vat->active);
        $this->assertSame(200, $vat->sort);
        $this->assertInstanceOf(CarbonImmutable::class, $vat->timestampX);
        $this->assertSame('2024-12-15T09:19:27+03:00', $vat->timestampX->toIso8601String());
    }

    #[Test]
    public function testGetOmitsEmptySelect(): void
    {
        $this->service('catalog.vat.get', ['id' => 4], ['item' => self::VAT_ITEM])->get(4);
    }

    #[Test]
    public function testListSendsFilterSelectOrderAndPagination(): void
    {
        $vats = $this->service(
            'catalog.vat.list',
            [
                'filter' => [['id', '=', 4]],
                'select' => ['id', 'name'],
                'order' => ['sort' => 'ASC'],
                'pagination' => ['page' => 2, 'limit' => 10],
            ],
            ['items' => [self::VAT_ITEM]]
        )->list(
            (new VatFilter())->id()->eq(4),
            (new VatSelectBuilder())->name(),
            ['sort' => 'ASC'],
            new VatListPagination(page: 2, limit: 10)
        )->getVats();

        $this->assertCount(1, $vats);
        $this->assertSame(4, $vats[0]->id);
    }

    #[Test]
    public function testListWithoutArgumentsSendsEmptyPayload(): void
    {
        $this->assertSame([], $this->service('catalog.vat.list', [], ['items' => []])->list()->getVats());
    }

    #[Test]
    public function testAddSendsFieldsAndReturnsCreatedItem(): void
    {
        $vat = $this->service(
            'catalog.vat.add',
            ['fields' => ['name' => 'VAT 7.5', 'rate' => 7.5, 'sort' => 500]],
            ['item' => ['id' => 38, 'name' => 'VAT 7.5', 'rate' => 7.5, 'sort' => 500]]
        )->add((new VatItemBuilder())->name('VAT 7.5')->rate(7.5)->sort(500))->vat();

        $this->assertSame(38, $vat->id);
        $this->assertSame(7.5, $vat->rate);
    }

    #[Test]
    public function testUpdateSendsIdAndFieldsAndReturnsUpdatedItem(): void
    {
        $vat = $this->service(
            'catalog.vat.update',
            ['id' => 38, 'fields' => ['rate' => 8.0]],
            ['item' => ['id' => 38, 'rate' => 8]]
        )->update(38, ['rate' => 8.0])->vat();

        $this->assertSame(8.0, $vat->rate);
    }

    #[Test]
    public function testDeleteSendsIdAndReadsNestedResult(): void
    {
        $this->assertTrue($this->service('catalog.vat.delete', ['id' => 38], ['result' => true])->delete(38)->isSuccess());
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $metadata = (new \ReflectionClass(Vat::class))->getAttributes(ApiServiceMetadata::class)[0]->newInstance();
        $this->assertSame(['catalog'], $metadata->scope->getScopeCodes());
        foreach (['get', 'list', 'add', 'update', 'delete'] as $method) {
            $endpoint = (new \ReflectionMethod(Vat::class, $method))->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();
            $this->assertSame('catalog.vat.' . $method, $endpoint->name);
            $this->assertSame(
                'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-' . $method . '-rest-v3.html',
                $endpoint->documentationUrl
            );
            $this->assertSame(ApiVersion::v3, $endpoint->apiVersion);
        }
    }

    private function service(string $method, array $parameters, array $result): Vat
    {
        $response = new Response(
            (new MockHttpClient(new JsonMockResponse(['result' => $result])))->request('POST', 'https://example.test'),
            new Command($method, $parameters),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($method, $parameters, ApiVersion::v3)->willReturn($response);

        return new Vat($core, new NullLogger());
    }
}
