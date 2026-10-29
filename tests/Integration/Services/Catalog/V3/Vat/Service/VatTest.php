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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3\Vat\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Service\Vat;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Service\VatFilter;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Service\VatItemBuilder;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Service\VatListPagination;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Service\VatSelectBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Vat::class)]
class VatTest extends TestCase
{
    private Vat $vatService;

    /** @var int[] */
    private array $createdIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->vatService = Factory::getServiceBuilder()->getCatalogScope()->v3()->vat();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            try {
                $this->vatService->delete($id);
            } catch (BaseException) {
                // already deleted by the test
            }
        }
    }

    #[Test]
    public function testAddReturnsCreatedVat(): void
    {
        $name = 'sdk v3 vat ' . time();
        $vat = $this->vatService->add((new VatItemBuilder())->name($name)->rate(7.5)->sort(500)->active(true))->vat();
        $this->createdIds[] = $vat->id;

        $this->assertGreaterThan(0, $vat->id);
        $this->assertSame($name, $vat->name);
        $this->assertSame(7.5, $vat->rate);
        $this->assertSame(500, $vat->sort);
        $this->assertTrue($vat->active);
        $this->assertInstanceOf(CarbonImmutable::class, $vat->timestampX);
    }

    #[Test]
    public function testGetReturnsSelectedFields(): void
    {
        $id = $this->createVat(10.0);

        $vat = $this->vatService->get($id, (new VatSelectBuilder())->name()->rate())->vat();

        $this->assertSame($id, $vat->id);
        $this->assertSame(10.0, $vat->rate);
    }

    #[Test]
    public function testListFiltersOrdersAndPaginates(): void
    {
        $firstId = $this->createVat(11.0);
        $secondId = $this->createVat(12.0);

        $filter = new VatFilter();
        $filter->id()->in([$firstId, $secondId]);
        $filter->active()->eq(true);

        $vats = $this->vatService->list(
            $filter,
            (new VatSelectBuilder())->rate(),
            ['id' => 'DESC'],
            new VatListPagination(limit: 1)
        )->getVats();

        $this->assertCount(1, $vats);
        $this->assertSame($secondId, $vats[0]->id);
        $this->assertSame(12.0, $vats[0]->rate);
    }

    #[Test]
    public function testUpdateReturnsUpdatedVat(): void
    {
        $id = $this->createVat(13.0);

        $vat = $this->vatService->update($id, (new VatItemBuilder())->rate(14.0)->active(false))->vat();

        $this->assertSame($id, $vat->id);
        $this->assertSame(14.0, $vat->rate);
        $this->assertFalse($vat->active);
    }

    #[Test]
    public function testDeleteRemovesVat(): void
    {
        $id = $this->createVat(15.0);

        $this->assertTrue($this->vatService->delete($id)->isSuccess());
        $this->assertSame([], $this->vatService->list((new VatFilter())->id()->eq($id))->getVats());
    }

    private function createVat(float $rate): int
    {
        $id = $this->vatService->add(
            (new VatItemBuilder())->name('sdk v3 vat ' . $rate . ' ' . time())->rate($rate)
        )->vat()->id;
        $this->createdIds[] = $id;

        return $id;
    }
}
