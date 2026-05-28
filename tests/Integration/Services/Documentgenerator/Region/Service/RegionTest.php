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
            'name' => 'SDK_TEST_' . $this->faker->uuid(),
            'code' => 'sdk_test_' . substr(md5($this->faker->uuid()), 0, 8),
        ])->getId();
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
        $this->regionService->delete($id);
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
        $this->regionService->delete($id);
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
        $this->regionService->delete($id);
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
            $this->regionService->update($id, ['name' => $updatedName])->isSuccess()
        );

        // Cleanup
        $this->regionService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $id = $this->createRegion();

        self::assertTrue($this->regionService->delete($id)->isSuccess());
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
        $this->regionService->delete($id);
    }
}

