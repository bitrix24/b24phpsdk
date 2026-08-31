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

namespace Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Numerator\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Documentgenerator\Numerator\Result\NumeratorItemResult;
use Bitrix24\SDK\Services\Documentgenerator\Numerator\Service\Numerator;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Faker;

/**
 * Class NumeratorTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Numerator\Service
 */
#[CoversMethod(Numerator::class, 'add')]
#[CoversMethod(Numerator::class, 'delete')]
#[CoversMethod(Numerator::class, 'get')]
#[CoversMethod(Numerator::class, 'list')]
#[CoversMethod(Numerator::class, 'update')]
#[CoversMethod(Numerator::class, 'count')]
#[\PHPUnit\Framework\Attributes\CoversClass(Numerator::class)]
class NumeratorTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Numerator $numeratorService;

    private Faker\Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->numeratorService = Fabric::getServiceBuilder()->getDocumentgeneratorScope()->numerator();
        $this->faker = Faker\Factory::create();
    }

    /**
     * Helper: create a test numerator and return its id.
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function createNumerator(): int
    {
        return $this->numeratorService->add([
            'name' => 'SDK_TEST_' . $this->faker->uuid(),
            'template' => 'TEST-{NUMBER}',
        ])->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $id = $this->createNumerator();
        self::assertGreaterThanOrEqual(1, $id);

        // Cleanup
        $this->numeratorService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $id = $this->createNumerator();

        $numeratorItemResult = $this->numeratorService->get($id)->numerator();
        self::assertInstanceOf(NumeratorItemResult::class, $numeratorItemResult);
        self::assertEquals($id, $numeratorItemResult->id);

        // Cleanup
        $this->numeratorService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $id = $this->createNumerator();

        $list = $this->numeratorService->list()->getNumerators();
        self::assertIsArray($list);
        self::assertGreaterThanOrEqual(1, count($list));

        // Cleanup
        $this->numeratorService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $id = $this->createNumerator();

        $updatedName = 'SDK_TEST_UPDATED_' . $this->faker->uuid();
        self::assertTrue(
            $this->numeratorService->update($id, ['name' => $updatedName])->isSuccess()
        );

        // Cleanup
        $this->numeratorService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $id = $this->createNumerator();

        self::assertTrue($this->numeratorService->delete($id)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCount(): void
    {
        $countBefore = $this->numeratorService->count();

        $id = $this->createNumerator();

        $countAfter = $this->numeratorService->count();
        self::assertEquals($countBefore + 1, $countAfter);

        // Cleanup
        $this->numeratorService->delete($id);
    }
}

