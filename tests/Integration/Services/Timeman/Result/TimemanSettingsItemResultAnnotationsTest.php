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
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimemanSettingsItemResult::class)]
class TimemanSettingsItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Timeman $timemanService;

    #[\Override]
    protected function setUp(): void
    {
        $this->timemanService = Factory::getServiceBuilder()->getTimemanScope()->timeman();
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

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $timemanSettingsItemResult,
            TimemanSettingsItemResult::class
        );
    }
}

