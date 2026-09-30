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
use Carbon\CarbonImmutable;

/**
 * @property-read int $id
 * @property-read bool|null $active
 * @property-read string $name
 * @property-read string $first_name
 * @property-read string $last_name
 * @property-read string $work_position
 * @property-read string $avatar
 * @property-read string $personal_gender
 * @property-read CarbonImmutable|null $last_activity_date
 */
class ReportUserItemResult extends AbstractAnnotatedItem
{
}
