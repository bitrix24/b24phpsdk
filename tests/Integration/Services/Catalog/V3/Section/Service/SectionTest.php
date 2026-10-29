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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\Section\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Catalog\V3\Section\Service\Section;
use Bitrix24\SDK\Services\Catalog\V3\Section\Service\SectionFilter;
use Bitrix24\SDK\Services\Catalog\V3\Section\Service\SectionItemBuilder;
use Bitrix24\SDK\Services\Catalog\V3\Section\Service\SectionListPagination;
use Bitrix24\SDK\Services\Catalog\V3\Section\Service\SectionSelectBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\ProductCatalogIblockTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Section::class)]
class SectionTest extends TestCase
{
    use ProductCatalogIblockTrait;

    private Section $sectionService;

    private int $iblockId;

    /** @var int[] */
    private array $createdIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->sectionService = Factory::getServiceBuilder()->getCatalogScope()->v3()->section();
        $this->iblockId = $this->getProductCatalogIblockId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach (array_reverse($this->createdIds) as $id) {
            try {
                $this->sectionService->delete($id);
            } catch (BaseException) {
                // already deleted by the test or together with its parent
            }
        }
    }

    #[Test]
    public function testAddReturnsCreatedRootSection(): void
    {
        $section = $this->sectionService->add(
            (new SectionItemBuilder())
                ->iblockId($this->iblockId)
                ->name('sdk v3 section')
                ->code('sdk-v3-section-' . random_int(1000, 9999))
                ->sort(300)
                ->active(true)
                ->description('created by sdk integration test')
                ->descriptionType('text')
        )->section();
        $this->createdIds[] = $section->id;

        $this->assertGreaterThan(0, $section->id);
        $this->assertSame($this->iblockId, $section->iblockId);
        $this->assertSame('sdk v3 section', $section->name);
        $this->assertSame(0, $section->iblockSectionId);
        $this->assertTrue($section->active);
        $this->assertSame(300, $section->sort);
        $this->assertSame('text', $section->descriptionType);
    }

    #[Test]
    public function testGetReturnsChildSection(): void
    {
        $parentId = $this->createSection();
        $childId = $this->createSection($parentId);

        $child = $this->sectionService->get($childId, (new SectionSelectBuilder())->iblockSectionId())->section();

        $this->assertSame($childId, $child->id);
        $this->assertSame($parentId, $child->iblockSectionId);
    }

    #[Test]
    public function testListFiltersOrdersAndPaginatesWithinIblock(): void
    {
        $parentId = $this->createSection();
        $this->createSection($parentId);
        $secondChildId = $this->createSection($parentId);

        $filter = new SectionFilter();
        $filter->iblockSectionId()->eq($parentId);
        $filter->active()->eq(true);

        $sections = $this->sectionService->list(
            $this->iblockId,
            $filter,
            (new SectionSelectBuilder())->iblockSectionId(),
            ['id' => 'DESC'],
            new SectionListPagination(limit: 1)
        )->getSections();

        $this->assertCount(1, $sections);
        $this->assertSame($secondChildId, $sections[0]->id);
        $this->assertSame($parentId, $sections[0]->iblockSectionId);
    }

    #[Test]
    public function testUpdateChangesNameAndDeactivates(): void
    {
        $id = $this->createSection();

        $section = $this->sectionService->update($id, (new SectionItemBuilder())->name('sdk v3 section updated')->active(false))->section();

        $this->assertSame($id, $section->id);
        $this->assertSame('sdk v3 section updated', $section->name);
        $this->assertFalse($section->active);
    }

    #[Test]
    public function testDeleteRemovesSectionWithSubsections(): void
    {
        $parentId = $this->createSection();
        $childId = $this->createSection($parentId);

        $filter = new SectionFilter();
        $filter->id()->in([$parentId, $childId]);

        $this->assertTrue($this->sectionService->delete($parentId)->isSuccess());
        $this->assertSame([], $this->sectionService->list($this->iblockId, $filter)->getSections());
    }

    private function createSection(?int $parentId = null): int
    {
        $builder = (new SectionItemBuilder())->iblockId($this->iblockId)->name('sdk v3 section ' . random_int(1000, 9999));
        if ($parentId !== null) {
            $builder->iblockSectionId($parentId);
        }

        $id = $this->sectionService->add($builder)->section()->id;
        $this->createdIds[] = $id;

        return $id;
    }
}
