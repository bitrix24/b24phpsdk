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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Measure\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Measure\Result\MeasureItemResult;
use Bitrix24\SDK\Services\Catalog\Measure\Service\Measure;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Faker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(MeasureItemResult::class)]
class MeasureItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Measure $measureService;

    private Faker\Generator $faker;

    #[\Override]
    protected function setUp(): void
    {
        $this->measureService = Factory::getServiceBuilder()->getCatalogScope()->measure();
        $this->faker = Faker\Factory::create();
    }

    /**
     * @return array<string, mixed>
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstMeasureRawItem(): array
    {
        $id = $this->measureService->add([
            'code' => $this->faker->unique()->numberBetween(100000, 999999),
            'measureTitle' => 'SDK_ANNOT_TEST_' . $this->faker->uuid(),
            'isDefault' => 'N',
        ])->getId();

        try {
            $rawItem = $this->measureService->get($id)
                ->getCoreResponse()->getResponseData()->getResult()['measure'] ?? [];
        } finally {
            $this->measureService->delete($id);
        }

        self::assertNotEmpty($rawItem, 'get() must return a measure item to run this test');

        return $rawItem;
    }

    #[Test]
    #[TestDox('all fields in MeasureItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->getFirstMeasureRawItem();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            MeasureItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in MeasureItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $rawItem = $this->getFirstMeasureRawItem();
        $measureItemResult = new MeasureItemResult($rawItem);

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $measureItemResult,
            MeasureItemResult::class
        );
    }
}
