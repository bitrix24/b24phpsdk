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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\V3\Section\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Catalog\V3\Section\Service\Section;
use Bitrix24\SDK\Services\Catalog\V3\Section\Service\SectionFilter;
use Bitrix24\SDK\Services\Catalog\V3\Section\Service\SectionItemBuilder;
use Bitrix24\SDK\Services\Catalog\V3\Section\Service\SectionListPagination;
use Bitrix24\SDK\Services\Catalog\V3\Section\Service\SectionSelectBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(Section::class)]
class SectionTest extends TestCase
{
    private const array SECTION_ITEM = [
        'id' => 10,
        'iblockId' => 24,
        'name' => 'Child',
        'iblockSectionId' => 8,
        'active' => 'N',
        'code' => 'child',
        'xmlId' => '',
        'sort' => 500,
        'description' => '',
        'descriptionType' => 'text',
    ];

    #[Test]
    public function testGetSendsIdAndSelectAndMapsItem(): void
    {
        $section = $this->service('catalog.section.get', ['id' => 10, 'select' => ['id', 'name', 'active']], ['item' => self::SECTION_ITEM])
            ->get(10, (new SectionSelectBuilder())->name()->active())
            ->section();

        $this->assertSame(10, $section->id);
        $this->assertSame(24, $section->iblockId);
        $this->assertSame('Child', $section->name);
        $this->assertSame(8, $section->iblockSectionId);
        $this->assertFalse($section->active);
        $this->assertSame('child', $section->code);
        $this->assertSame(500, $section->sort);
        $this->assertSame('text', $section->descriptionType);
    }

    #[Test]
    public function testGetOmitsEmptySelect(): void
    {
        $this->service('catalog.section.get', ['id' => 10], ['item' => self::SECTION_ITEM])->get(10);
    }

    #[Test]
    public function testListPrependsRequiredIblockFilterToBuilderFilter(): void
    {
        $filter = new SectionFilter();
        $filter->active()->eq(true);

        $sections = $this->service(
            'catalog.section.list',
            [
                'filter' => [['iblockId', '=', 24], ['active', '=', 'Y']],
                'select' => ['id', 'name'],
                'order' => ['sort' => 'ASC'],
                'pagination' => ['page' => 1, 'limit' => 50],
            ],
            ['items' => [self::SECTION_ITEM]]
        )->list(
            24,
            $filter,
            (new SectionSelectBuilder())->name(),
            ['sort' => 'ASC'],
            new SectionListPagination(page: 1, limit: 50)
        )->getSections();

        $this->assertCount(1, $sections);
        $this->assertSame(10, $sections[0]->id);
    }

    #[Test]
    public function testListPrependsRequiredIblockFilterToArrayFilter(): void
    {
        $this->service(
            'catalog.section.list',
            ['filter' => [['iblockId', '=', 24], ['iblockSectionId', '=', 8]]],
            ['items' => []]
        )->list(24, [['iblockSectionId', '=', 8]]);
    }

    #[Test]
    public function testListWithOnlyIblockSendsIblockFilter(): void
    {
        $this->assertSame(
            [],
            $this->service('catalog.section.list', ['filter' => [['iblockId', '=', 24]]], ['items' => []])->list(24)->getSections()
        );
    }

    #[Test]
    public function testAddSendsFieldsWithYesNoActiveAndReturnsCreatedItem(): void
    {
        $section = $this->service(
            'catalog.section.add',
            ['fields' => ['iblockId' => 24, 'name' => 'Root', 'active' => 'Y', 'sort' => 300]],
            ['item' => ['id' => 8, 'iblockId' => 24, 'name' => 'Root', 'iblockSectionId' => 0, 'active' => 'Y', 'sort' => 300]]
        )->add((new SectionItemBuilder())->iblockId(24)->name('Root')->active(true)->sort(300))->section();

        $this->assertSame(8, $section->id);
        $this->assertSame(0, $section->iblockSectionId);
        $this->assertTrue($section->active);
    }

    #[Test]
    public function testUpdateSendsIdAndFieldsAndReturnsUpdatedItem(): void
    {
        $section = $this->service(
            'catalog.section.update',
            ['id' => 8, 'fields' => ['active' => 'N', 'name' => 'Root 2']],
            ['item' => ['id' => 8, 'name' => 'Root 2', 'active' => 'N']]
        )->update(8, (new SectionItemBuilder())->active(false)->name('Root 2'))->section();

        $this->assertSame('Root 2', $section->name);
        $this->assertFalse($section->active);
    }

    #[Test]
    public function testDeleteSendsIdAndReadsNestedResult(): void
    {
        $this->assertTrue($this->service('catalog.section.delete', ['id' => 8], ['result' => true])->delete(8)->isSuccess());
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $metadata = (new \ReflectionClass(Section::class))->getAttributes(ApiServiceMetadata::class)[0]->newInstance();
        $this->assertSame(['catalog'], $metadata->scope->getScopeCodes());
        foreach (['get', 'list', 'add', 'update', 'delete'] as $method) {
            $endpoint = (new \ReflectionMethod(Section::class, $method))->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();
            $this->assertSame('catalog.section.' . $method, $endpoint->name);
            $this->assertSame(
                'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-' . $method . '-rest-v3.html',
                $endpoint->documentationUrl
            );
            $this->assertSame(ApiVersion::v3, $endpoint->apiVersion);
        }
    }

    private function service(string $method, array $parameters, array $result): Section
    {
        $response = new Response(
            (new MockHttpClient(new JsonMockResponse(['result' => $result])))->request('POST', 'https://example.test'),
            new Command($method, $parameters),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($method, $parameters, ApiVersion::v3)->willReturn($response);

        return new Section($core, new NullLogger());
    }
}
