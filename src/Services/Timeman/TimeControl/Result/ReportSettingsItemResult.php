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
 * @property-read int $user_id
 * @property-read bool $user_admin
 * @property-read bool $user_head
 * @property-read array<array{id: string, name: string}> $departments
 * @property-read int $minimum_idle_for_report
 * @property-read string $report_view_type
 */
class ReportSettingsItemResult extends AbstractAnnotatedItem
{
}
