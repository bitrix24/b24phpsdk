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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\V3\Measure\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Service\Measure;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Service\MeasureFilter;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Service\MeasureItemBuilder;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Service\MeasureListPagination;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Service\MeasureSelectBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(Measure::class)]
class MeasureTest extends TestCase
{
    private const array MEASURE_ITEM = [
        'id' => 1,
        'code' => 6,
        'measureTitle' => 'Metre',
        'symbol' => 'm',
        'symbolIntl' => 'm',
        'symbolLetterIntl' => 'MTR',
        'isDefault' => false,
    ];

    #[Test]
    public function testGetSendsIdAndSelectAndMapsItem(): void
    {
        $measure = $this->service('catalog.measure.get', ['id' => 1, 'select' => ['id', 'code', 'measureTitle']], ['item' => self::MEASURE_ITEM])
            ->get(1, (new MeasureSelectBuilder())->code()->measureTitle())
            ->measure();

        $this->assertSame(1, $measure->id);
        $this->assertSame(6, $measure->code);
        $this->assertSame('Metre', $measure->measureTitle);
        $this->assertSame('m', $measure->symbol);
        $this->assertSame('m', $measure->symbolIntl);
        $this->assertSame('MTR', $measure->symbolLetterIntl);
        $this->assertFalse($measure->isDefault);
    }

    #[Test]
    public function testGetOmitsEmptySelect(): void
    {
        $this->service('catalog.measure.get', ['id' => 1], ['item' => self::MEASURE_ITEM])->get(1);
    }

    #[Test]
    public function testListSendsFilterSelectOrderAndPagination(): void
    {
        $filter = new MeasureFilter();
        $filter->isDefault()->eq(true);

        $measures = $this->service(
            'catalog.measure.list',
            [
                'filter' => [['isDefault', '=', true]],
                'select' => ['id', 'symbolIntl'],
                'order' => ['code' => 'DESC'],
                'pagination' => ['limit' => 5, 'offset' => 10],
            ],
            ['items' => [self::MEASURE_ITEM]]
        )->list(
            $filter,
            (new MeasureSelectBuilder())->symbolIntl(),
            ['code' => 'DESC'],
            new MeasureListPagination(limit: 5, offset: 10)
        )->getMeasures();

        $this->assertCount(1, $measures);
        $this->assertSame(6, $measures[0]->code);
    }

    #[Test]
    public function testListWithoutArgumentsSendsEmptyPayload(): void
    {
        $this->assertSame([], $this->service('catalog.measure.list', [], ['items' => []])->list()->getMeasures());
    }

    #[Test]
    public function testAddSendsFieldsAndReturnsCreatedItem(): void
    {
        $measure = $this->service(
            'catalog.measure.add',
            ['fields' => ['code' => 9917, 'measureTitle' => 'Probe', 'symbolLetterIntl' => 'PRB']],
            ['item' => ['id' => 10, 'code' => 9917, 'measureTitle' => 'Probe', 'symbolLetterIntl' => 'PRB', 'isDefault' => false]]
        )->add((new MeasureItemBuilder())->code(9917)->measureTitle('Probe')->symbolLetterIntl('PRB'))->measure();

        $this->assertSame(10, $measure->id);
        $this->assertSame(9917, $measure->code);
    }

    #[Test]
    public function testUpdateSendsIdAndFieldsAndReturnsUpdatedItem(): void
    {
        $measure = $this->service(
            'catalog.measure.update',
            ['id' => 10, 'fields' => ['measureTitle' => 'Probe 2']],
            ['item' => ['id' => 10, 'measureTitle' => 'Probe 2']]
        )->update(10, ['measureTitle' => 'Probe 2'])->measure();

        $this->assertSame('Probe 2', $measure->measureTitle);
    }

    #[Test]
    public function testDeleteSendsIdAndReadsNestedResult(): void
    {
        $this->assertTrue($this->service('catalog.measure.delete', ['id' => 10], ['result' => true])->delete(10)->isSuccess());
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $metadata = (new \ReflectionClass(Measure::class))->getAttributes(ApiServiceMetadata::class)[0]->newInstance();
        $this->assertSame(['catalog'], $metadata->scope->getScopeCodes());
        foreach (['get', 'list', 'add', 'update', 'delete'] as $method) {
            $endpoint = (new \ReflectionMethod(Measure::class, $method))->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();
            $this->assertSame('catalog.measure.' . $method, $endpoint->name);
            $this->assertSame(
                'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-' . $method . '-rest-v3.html',
                $endpoint->documentationUrl
            );
            $this->assertSame(ApiVersion::v3, $endpoint->apiVersion);
        }
    }

    private function service(string $method, array $parameters, array $result): Measure
    {
        $response = new Response(
            (new MockHttpClient(new JsonMockResponse(['result' => $result])))->request('POST', 'https://example.test'),
            new Command($method, $parameters),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($method, $parameters, ApiVersion::v3)->willReturn($response);

        return new Measure($core, new NullLogger());
    }
}
