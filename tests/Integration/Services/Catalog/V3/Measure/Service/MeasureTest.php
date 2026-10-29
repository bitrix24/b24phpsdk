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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\Measure\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Service\Measure;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Service\MeasureFilter;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Service\MeasureItemBuilder;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Service\MeasureListPagination;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Service\MeasureSelectBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Measure::class)]
class MeasureTest extends TestCase
{
    private Measure $measureService;

    /** @var int[] */
    private array $createdIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->measureService = Factory::getServiceBuilder()->getCatalogScope()->v3()->measure();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            try {
                $this->measureService->delete($id);
            } catch (BaseException) {
                // already deleted by the test
            }
        }
    }

    #[Test]
    public function testAddReturnsCreatedMeasure(): void
    {
        $code = $this->uniqueCode();
        $measure = $this->measureService->add(
            (new MeasureItemBuilder())
                ->code($code)
                ->measureTitle('sdk v3 measure')
                ->symbol('svm')
                ->symbolIntl('svm')
                ->symbolLetterIntl('SVM')
                ->isDefault(false)
        )->measure();
        $this->createdIds[] = $measure->id;

        $this->assertGreaterThan(0, $measure->id);
        $this->assertSame($code, $measure->code);
        $this->assertSame('sdk v3 measure', $measure->measureTitle);
        $this->assertSame('SVM', $measure->symbolLetterIntl);
        $this->assertFalse($measure->isDefault);
    }

    #[Test]
    public function testGetReturnsSelectedFields(): void
    {
        [$id, $code] = $this->createMeasure();

        $measure = $this->measureService->get($id, (new MeasureSelectBuilder())->code())->measure();

        $this->assertSame($id, $measure->id);
        $this->assertSame($code, $measure->code);
    }

    #[Test]
    public function testListFiltersOrdersAndPaginates(): void
    {
        [$firstId] = $this->createMeasure();
        [$secondId, $secondCode] = $this->createMeasure();

        $filter = new MeasureFilter();
        $filter->id()->in([$firstId, $secondId]);
        $filter->isDefault()->eq(false);

        $measures = $this->measureService->list(
            $filter,
            (new MeasureSelectBuilder())->code(),
            ['id' => 'DESC'],
            new MeasureListPagination(limit: 1)
        )->getMeasures();

        $this->assertCount(1, $measures);
        $this->assertSame($secondId, $measures[0]->id);
        $this->assertSame($secondCode, $measures[0]->code);
    }

    #[Test]
    public function testUpdateReturnsUpdatedMeasure(): void
    {
        [$id] = $this->createMeasure();

        $measure = $this->measureService->update($id, (new MeasureItemBuilder())->measureTitle('sdk v3 measure updated'))->measure();

        $this->assertSame($id, $measure->id);
        $this->assertSame('sdk v3 measure updated', $measure->measureTitle);
    }

    #[Test]
    public function testDeleteRemovesMeasure(): void
    {
        [$id] = $this->createMeasure();

        $filter = new MeasureFilter();
        $filter->id()->eq($id);

        $this->assertTrue($this->measureService->delete($id)->isSuccess());
        $this->assertSame([], $this->measureService->list($filter)->getMeasures());
    }

    /**
     * @return array{int, int} measure id and code
     */
    private function createMeasure(): array
    {
        $code = $this->uniqueCode();
        $id = $this->measureService->add(
            (new MeasureItemBuilder())->code($code)->measureTitle('sdk v3 measure ' . $code)
        )->measure()->id;
        $this->createdIds[] = $id;

        return [$id, $code];
    }

    // code is unique per portal, so every created measure gets a random one
    private function uniqueCode(): int
    {
        return random_int(100000, 999999);
    }
}
