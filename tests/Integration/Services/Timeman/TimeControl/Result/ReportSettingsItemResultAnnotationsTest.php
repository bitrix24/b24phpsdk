<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Timeman\TimeControl\Result;

use Bitrix24\SDK\Services\Timeman\TimeControl\Service\TimeControl;
use Bitrix24\SDK\Services\Timeman\TimeControl\Result\ReportSettingsItemResult;
use Bitrix24\SDK\Tests\Integration\Services\Timeman\ReadOnlyCoreTrait;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/** No fields endpoint exists: verify live keys and annotated getter casts. Empty lists are valid. */
#[CoversClass(ReportSettingsItemResult::class)]
class ReportSettingsItemResultAnnotationsTest extends TestCase
{
    use ReadOnlyCoreTrait;
    use CustomBitrix24Assertions;

    public function testAllSystemFieldsAnnotated(): void
    {
        $timeControl = new TimeControl($this->getReadOnlyCore(), new NullLogger());
        $result = $timeControl->getReportSettings();
        $items = [$result->getCoreResponse()->getResponseData()->getResult()];
        self::assertIsArray($items);
        foreach ($items as $item) {
            $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($item), ReportSettingsItemResult::class);
        }
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $timeControl = new TimeControl($this->getReadOnlyCore(), new NullLogger());
        $result = $timeControl->getReportSettings();
        $items = [$result->getSettings()];
        self::assertIsArray($items);
        foreach ($items as $item) {
            $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($item, ReportSettingsItemResult::class);
        }
    }
}
