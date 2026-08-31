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

use Bitrix24\SDK\Services\Timeman\Result\TimemanSettingsItemResult;
use Bitrix24\SDK\Services\Timeman\Service\Timeman;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Typhoon\Reflection\TyphoonReflector;
use function Typhoon\Type\stringify;

#[CoversClass(TimemanSettingsItemResult::class)]
class TimemanSettingsItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Timeman $timemanService;

    #[\Override]
    protected function setUp(): void
    {
        $this->timemanService = Fabric::getServiceBuilder()->getTimemanScope()->timeman();
    }

    #[Test]
    #[TestDox('all fields in TimemanSettingsItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawResult = $this->timemanService->settings()->getCoreResponse()
            ->getResponseData()->getResult();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawResult),
            TimemanSettingsItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in TimemanSettingsItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $timemanSettingsItemResult = $this->timemanService->settings()->getSettings();

        $collection = TyphoonReflector::build()
            ->reflectClass(TimemanSettingsItemResult::class)
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
            $value    = $timemanSettingsItemResult->$propName;

            // null is always valid for nullable types
            if (str_contains($typeStr, 'null') && $value === null) {
                continue;
            }

            $message = sprintf(
                'field «%s» in «%s» annotated as «%s» but actual PHP type is «%s»',
                $propName,
                TimemanSettingsItemResult::class,
                $typeStr,
                get_debug_type($value)
            );

            match (true) {
                str_contains($typeStr, 'array')  => $this->assertIsArray($value, $message),
                str_contains($typeStr, 'bool')   => $this->assertIsBool($value, $message),
                str_contains($typeStr, 'int')    => $this->assertIsInt($value, $message),
                str_contains($typeStr, 'float')  => $this->assertIsFloat($value, $message),
                str_contains($typeStr, 'string') => $this->assertIsString($value, $message),
                default                          => $this->assertInstanceOf($typeStr, $value, $message),
            };
        }
    }
}

