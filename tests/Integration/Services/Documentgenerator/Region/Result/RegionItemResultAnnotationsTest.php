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

namespace Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Region\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Documentgenerator\Region\Result\RegionItemResult;
use Bitrix24\SDK\Services\Documentgenerator\Region\Service\Region;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegionItemResult::class)]
class RegionItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Region $regionService;

    private Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->regionService = Factory::getServiceBuilder()->getDocumentgeneratorScope()->region();
        $this->faker = FakerFactory::create();
    }

    /**
     * Helper: create a region, fetch it via get() to obtain the full field set, then delete it.
     *
     * NOTE: documentgenerator.region.list() returns a reduced set of fields (code, languageId, title)
     * without the numeric id. documentgenerator.region.get() returns the full set (id, languageId, name, code).
     * We therefore validate annotations against the get() response.
     *
     * @return array<string, mixed>
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstRegionRawItem(): array
    {
        $id = $this->regionService->add([
            'languageId' => 'en',
            'title'      => 'SDK_ANNOT_TEST_' . $this->faker->uuid(),
        ])->getId();

        $rawItem = $this->regionService->get($id)
            ->getCoreResponse()->getResponseData()->getResult()['region'] ?? [];

        try {
            $this->regionService->delete($id);
        } catch (\Bitrix24\SDK\Core\Exceptions\BaseException) {
            // Server-side delete bug on some portals; cleanup failure must not affect annotations test
        }

        self::assertNotEmpty($rawItem, 'get() must return a region item to run this test');

        return $rawItem;
    }

    #[Test]
    #[TestDox('all fields in RegionItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->getFirstRegionRawItem();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            RegionItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in RegionItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $rawItem = $this->getFirstRegionRawItem();
        $regionItemResult = new RegionItemResult($rawItem);

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $regionItemResult,
            RegionItemResult::class
        );
    }
}
