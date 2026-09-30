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

namespace Bitrix24\SDK\Tests\Unit\Services\Timeman;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\Timeman\NetworkRange\Result\NetworkRangeItemResult;
use Bitrix24\SDK\Services\Timeman\NetworkRange\Result\NetworkRangeMatchItemResult;
use Bitrix24\SDK\Services\Timeman\TimeControl\Result\ReportItemResult;
use Bitrix24\SDK\Services\Timeman\TimeControl\Result\ReportSettingsItemResult;
use Bitrix24\SDK\Services\Timeman\TimeControl\Result\ReportUserItemResult;
use Bitrix24\SDK\Services\Timeman\TimeControl\Result\TimeControlSettingsItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NetworkRangeItemResult::class)]
#[CoversClass(NetworkRangeMatchItemResult::class)]
#[CoversClass(ReportItemResult::class)]
#[CoversClass(ReportSettingsItemResult::class)]
#[CoversClass(ReportUserItemResult::class)]
#[CoversClass(TimeControlSettingsItemResult::class)]
class LegacyResultFixturesTest extends TestCase
{
    use CustomBitrix24Assertions;

    /** @param class-string<AbstractAnnotatedItem> $class */
    #[DataProvider('documentedObjects')]
    public function testDocumentedFieldsAndCasts(string $class, array $raw): void
    {
        $fields = $class === ReportUserItemResult::class ? $raw + ['active' => null] : $raw;
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), $class);
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(new $class($raw), $class);
    }

    public function testNewServicesDeclareTimemanScope(): void
    {
        foreach ([\Bitrix24\SDK\Services\Timeman\NetworkRange\Service\NetworkRange::class, \Bitrix24\SDK\Services\Timeman\TimeControl\Service\TimeControl::class] as $class) {
            self::assertCount(1, (new \ReflectionClass($class))->getAttributes(\Bitrix24\SDK\Attributes\ApiServiceMetadata::class));
        }
    }

    public static function documentedObjects(): array
    {
        return [
            'range' => [NetworkRangeItemResult::class, ['ip_range' => '10.0.0.0-10.255.255.255', 'name' => 'Office']],
            'match' => [NetworkRangeMatchItemResult::class, ['ip' => '10.0.0.1', 'range' => '10.0.0.0-10.255.255.255', 'name' => 'Office']],
            'report' => [ReportItemResult::class, ['month_title' => 'May', 'date_start' => '2025-05-01T00:00:00+03:00', 'date_finish' => '2025-05-31T23:59:59+03:00', 'days' => []]],
            'report settings' => [ReportSettingsItemResult::class, ['active' => false, 'user_id' => '503', 'user_admin' => false, 'user_head' => true, 'departments' => [['id' => '9', 'name' => 'Marketing']], 'minimum_idle_for_report' => '15', 'report_view_type' => 'head']],
            'user' => [ReportUserItemResult::class, ['id' => '503', 'active' => true, 'name' => 'Test User', 'first_name' => 'Test', 'last_name' => 'User', 'work_position' => 'Developer', 'avatar' => '', 'personal_gender' => 'F', 'last_activity_date' => '2025-05-29T16:41:00+03:00']],
            'user list without active' => [ReportUserItemResult::class, ['id' => '503', 'name' => 'Test User', 'first_name' => 'Test', 'last_name' => 'User', 'work_position' => 'Developer', 'avatar' => '', 'personal_gender' => 'F', 'last_activity_date' => null]],
            'settings' => [TimeControlSettingsItemResult::class, ['active' => false, 'minimum_idle_for_report' => '15', 'register_offline' => true, 'register_idle' => true, 'register_desktop' => true, 'report_request_type' => 'all', 'report_request_users' => [], 'report_simple_type' => 'all', 'report_simple_users' => [], 'report_full_type' => 'all', 'report_full_users' => []]],
        ];
    }
}
