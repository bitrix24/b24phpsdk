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

namespace Bitrix24\SDK\Tests\Integration\Services\Timeman\Result;

use Bitrix24\SDK\Services\Timeman\Result\WorkdayItemResult;
use Bitrix24\SDK\Services\Timeman\Service\Timeman;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Typhoon\Reflection\TyphoonReflector;
use function Typhoon\Type\stringify;

#[CoversClass(WorkdayItemResult::class)]
class WorkdayItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Timeman $timemanService;

    #[\Override]
    protected function setUp(): void
    {
        $this->timemanService = Fabric::getServiceBuilder()->getTimemanScope()->timeman();
        // Ensure a workday is open so the API returns all fields (not just STATUS)
        $this->timemanService->open();
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Close the workday opened in setUp to clean up
        $this->timemanService->close();
    }

    #[Test]
    #[TestDox('all fields in WorkdayItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawResult = $this->timemanService->status()->getCoreResponse()
            ->getResponseData()->getResult();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawResult),
            WorkdayItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in WorkdayItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $workdayItemResult = $this->timemanService->status()->getWorkday();

        // We do the type-cast check inline rather than via assertBitrix24ResultItemFieldsTypeCastMatchAnnotations
        // because that method passes the raw union-type string (e.g. "Carbon\CarbonImmutable|null")
        // to assertInstanceOf(), which PHP rejects as an invalid class name.
        $collection = TyphoonReflector::build()
            ->reflectClass(WorkdayItemResult::class)
            ->properties();

        foreach ($collection as $meta) {
            if (!$meta->isAnnotated()) {
                continue;
            }

            if ($meta->isNative()) {
                continue;
            }

            $propName = $meta->id->name;
            $typeStr  = stringify($meta->type());
            $value    = $workdayItemResult->$propName;

            // null is always valid for nullable types
            if (str_contains($typeStr, 'null') && $value === null) {
                continue;
            }

            $message = sprintf(
                'field «%s» in «%s» annotated as «%s» but actual PHP type is «%s»',
                $propName,
                WorkdayItemResult::class,
                $typeStr,
                get_debug_type($value)
            );

            match (true) {
                str_contains($typeStr, CarbonImmutable::class) => $this->assertInstanceOf(CarbonImmutable::class, $value, $message),
                str_contains($typeStr, 'array')                => $this->assertIsArray($value, $message),
                str_contains($typeStr, 'bool')                 => $this->assertIsBool($value, $message),
                str_contains($typeStr, 'int')                  => $this->assertIsInt($value, $message),
                str_contains($typeStr, 'float')                => $this->assertIsFloat($value, $message),
                str_contains($typeStr, 'string')               => $this->assertIsString($value, $message),
                default                                        => $this->assertInstanceOf($typeStr, $value, $message),
            };
        }
    }
}
