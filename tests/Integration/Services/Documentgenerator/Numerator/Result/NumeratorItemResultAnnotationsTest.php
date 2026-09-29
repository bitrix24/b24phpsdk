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

namespace Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Numerator\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Documentgenerator\Numerator\Result\NumeratorItemResult;
use Bitrix24\SDK\Services\Documentgenerator\Numerator\Service\Numerator;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(NumeratorItemResult::class)]
class NumeratorItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Numerator $numeratorService;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->numeratorService = Fabric::getServiceBuilder()->getDocumentgeneratorScope()->numerator();
    }

    /**
     * Helper: get raw data for the first numerator from the list.
     *
     * @return array<string, mixed>
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstNumeratorRawItem(): array
    {
        $result = $this->numeratorService->list()
            ->getCoreResponse()->getResponseData()->getResult();

        $numerators = $result['numerators'] ?? [];
        self::assertNotEmpty($numerators, 'At least one numerator must exist to run this test');

        return array_values($numerators)[0];
    }

    #[Test]
    #[TestDox('all fields in NumeratorItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->getFirstNumeratorRawItem();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            NumeratorItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in NumeratorItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $rawItem = $this->getFirstNumeratorRawItem();
        $numeratorItemResult = new NumeratorItemResult($rawItem);

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $numeratorItemResult,
            NumeratorItemResult::class
        );
    }
}

