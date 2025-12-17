<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <titarx@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Documentgenerator\Numerator\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Result\NumeratorItemResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Service\Numerator;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Faker;

/**
 * Class NumeratorTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Documentgenerator\Numerator\Service
 */
#[CoversMethod(Numerator::class, 'add')]
#[CoversMethod(Numerator::class, 'delete')]
#[CoversMethod(Numerator::class, 'get')]
#[CoversMethod(Numerator::class, 'list')]
#[CoversMethod(Numerator::class, 'update')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Service\Numerator::class)]
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
        $this->numeratorService = Factory::getServiceBuilder()->getCRMScope()->documentgeneratorNumerator();
        $this->faker = Faker\Factory::create();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $name = 'num-' . $this->faker->uuid();
        $id = $this->numeratorService->add([
            'name' => $name,
            'template' => 'N-{NUMBER}',
        ])->getId();

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
        $name = 'num-' . $this->faker->uuid();
        $id = $this->numeratorService->add([
            'name' => $name,
            'template' => 'G-{NUMBER}',
        ])->getId();

        $numeratorItemResult = $this->numeratorService->get($id)->numerator();
        self::assertInstanceOf(NumeratorItemResult::class, $numeratorItemResult);
        self::assertEquals($id, $numeratorItemResult->id);
        self::assertEquals($name, $numeratorItemResult->name);

        // Cleanup
        $this->numeratorService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $name = 'num-' . $this->faker->uuid();
        $id = $this->numeratorService->add([
            'name' => $name,
            'template' => 'L-{NUMBER}',
        ])->getId();

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
        $name = 'num-' . $this->faker->uuid();
        $id = $this->numeratorService->add([
            'name' => $name,
            'template' => 'U-{NUMBER}',
        ])->getId();

        $newName = $name . '-updated';
        self::assertTrue(
            $this->numeratorService->update($id, [
                'name' => $newName,
                'template' => 'U2-{NUMBER}',
            ])->isSuccess()
        );

        self::assertEquals($newName, $this->numeratorService->get($id)->numerator()->name);

        // Cleanup
        $this->numeratorService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $id = $this->numeratorService->add([
            'name' => 'num-' . $this->faker->uuid(),
            'template' => 'D-{NUMBER}',
        ])->getId();

        self::assertTrue($this->numeratorService->delete($id)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCount(): void
    {
        $countBefore = $this->numeratorService->count();

        $id = $this->numeratorService->add([
            'name' => 'num-' . $this->faker->uuid(),
            'template' => 'C-{NUMBER}',
        ])->getId();

        $countAfter = $this->numeratorService->count();
        self::assertEquals($countBefore + 1, $countAfter);

        // Cleanup
        $this->numeratorService->delete($id);
    }
}
