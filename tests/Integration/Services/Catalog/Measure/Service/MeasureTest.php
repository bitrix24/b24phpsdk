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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Measure\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Measure\Result\MeasureItemResult;
use Bitrix24\SDK\Services\Catalog\Measure\Service\Measure;
use Bitrix24\SDK\Tests\Integration\Factory;
use Faker;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Measure::class, 'add')]
#[CoversMethod(Measure::class, 'update')]
#[CoversMethod(Measure::class, 'get')]
#[CoversMethod(Measure::class, 'list')]
#[CoversMethod(Measure::class, 'delete')]
#[CoversMethod(Measure::class, 'fields')]
class MeasureTest extends TestCase
{
    private Measure $measureService;

    private Faker\Generator $faker;

    #[\Override]
    protected function setUp(): void
    {
        $this->measureService = Factory::getServiceBuilder()->getCatalogScope()->measure();
        $this->faker = Faker\Factory::create();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function createMeasure(): int
    {
        return $this->measureService->add([
            'code' => $this->faker->unique()->numberBetween(100000, 999999),
            'measureTitle' => 'SDK_TEST_' . $this->faker->uuid(),
            'isDefault' => 'N',
        ])->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $id = $this->createMeasure();
        self::assertGreaterThan(0, $id);

        $this->measureService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $id = $this->createMeasure();

        try {
            $measureItemResult = $this->measureService->get($id)->measure();
            self::assertInstanceOf(MeasureItemResult::class, $measureItemResult);
            self::assertEquals($id, $measureItemResult->id);
        } finally {
            $this->measureService->delete($id);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $id = $this->createMeasure();

        try {
            $measuresResult = $this->measureService->list();
            self::assertGreaterThanOrEqual(1, count($measuresResult->getMeasures()));
            self::assertGreaterThanOrEqual(1, $measuresResult->getTotal());
        } finally {
            $this->measureService->delete($id);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $id = $this->createMeasure();

        try {
            $updatedTitle = 'SDK_TEST_UPDATED_' . $this->faker->uuid();
            self::assertTrue(
                $this->measureService->update($id, ['measureTitle' => $updatedTitle])->isSuccess()
            );

            $updatedMeasure = $this->measureService->get($id)->measure();
            self::assertEquals($updatedTitle, $updatedMeasure->measureTitle);
        } finally {
            $this->measureService->delete($id);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $id = $this->createMeasure();

        self::assertTrue($this->measureService->delete($id)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        $fields = $this->measureService->fields()->getFieldsDescription();

        self::assertArrayHasKey('measure', $fields);
        self::assertArrayHasKey('id', $fields['measure']);
        self::assertArrayHasKey('code', $fields['measure']);
        self::assertArrayHasKey('measureTitle', $fields['measure']);
    }
}
