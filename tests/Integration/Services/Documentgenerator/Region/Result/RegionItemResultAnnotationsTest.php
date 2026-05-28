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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegionItemResult::class)]
class RegionItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Region $regionService;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->regionService = Factory::getServiceBuilder()->getDocumentgeneratorScope()->region();
    }

    /**
     * Helper: get raw data for the first region from the list.
     *
     * @return array<string, mixed>
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstRegionRawItem(): array
    {
        $result = $this->regionService->list()
            ->getCoreResponse()->getResponseData()->getResult();

        $regions = $result['regions'] ?? [];
        self::assertNotEmpty($regions, 'At least one region must exist to run this test');

        return array_values($regions)[0];
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

