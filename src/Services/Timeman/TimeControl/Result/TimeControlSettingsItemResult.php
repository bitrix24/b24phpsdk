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

namespace Bitrix24\SDK\Services\Timeman\TimeControl\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read bool $active
 * @property-read int $minimum_idle_for_report
 * @property-read bool $register_offline
 * @property-read bool $register_idle
 * @property-read bool $register_desktop
 * @property-read string $report_request_type
 * @property-read array<int> $report_request_users
 * @property-read string $report_simple_type
 * @property-read array<int> $report_simple_users
 * @property-read string $report_full_type
 * @property-read array<int> $report_full_users
 */
class TimeControlSettingsItemResult extends AbstractAnnotatedItem
{
}
