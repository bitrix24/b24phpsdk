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

namespace Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Region\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Documentgenerator\Region\Result\RegionItemResult;
use Bitrix24\SDK\Services\Documentgenerator\Region\Service\Region;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Faker;

/**
 * Class RegionTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Region\Service
 */
#[CoversMethod(Region::class, 'add')]
#[CoversMethod(Region::class, 'delete')]
#[CoversMethod(Region::class, 'get')]
#[CoversMethod(Region::class, 'list')]
#[CoversMethod(Region::class, 'update')]
#[CoversMethod(Region::class, 'count')]
#[\PHPUnit\Framework\Attributes\CoversClass(Region::class)]
class RegionTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Region $regionService;

    private Faker\Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->regionService = Factory::getServiceBuilder()->getDocumentgeneratorScope()->region();
        $this->faker = Faker\Factory::create();
    }

    /**
     * Helper: create a test region and return its id.
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function createRegion(): int
    {
        return $this->regionService->add([
            'languageId' => 'en',
            'title' => 'SDK_TEST_' . $this->faker->uuid(),
        ])->getId();
    }

    /**
     * Helper: silently delete a region.
     * documentgenerator.region.delete has a known server-side bug on some portals
     * (class "bitrix\main\orm\eventresult" not found).
     * Cleanup failures must not break unrelated test assertions.
     */
    private function safeDelete(int $id): void
    {
        try {
            $this->regionService->delete($id);
        } catch (BaseException) {
            // Server-side delete bug; ignored during cleanup
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $id = $this->createRegion();
        self::assertGreaterThanOrEqual(1, $id);

        // Cleanup
        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $id = $this->createRegion();

        $regionItemResult = $this->regionService->get($id)->region();
        self::assertInstanceOf(RegionItemResult::class, $regionItemResult);
        self::assertEquals($id, $regionItemResult->id);

        // Cleanup
        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $id = $this->createRegion();

        $list = $this->regionService->list()->getRegions();
        self::assertIsArray($list);
        self::assertGreaterThanOrEqual(1, count($list));

        // Cleanup
        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $id = $this->createRegion();

        $updatedName = 'SDK_TEST_UPDATED_' . $this->faker->uuid();
        self::assertTrue(
            $this->regionService->update($id, ['title' => $updatedName])->isSuccess()
        );

        // Cleanup
        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $id = $this->createRegion();

        try {
            $result = $this->regionService->delete($id);
            self::assertTrue($result->isSuccess());
        } catch (BaseException $baseException) {
            $this->markTestSkipped(
                'documentgenerator.region.delete has a known server-side bug on this portal: ' . $baseException->getMessage()
            );
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCount(): void
    {
        $countBefore = $this->regionService->count();

        $id = $this->createRegion();

        $countAfter = $this->regionService->count();
        self::assertEquals($countBefore + 1, $countAfter);

        // Cleanup
        $this->safeDelete($id);
    }
}
