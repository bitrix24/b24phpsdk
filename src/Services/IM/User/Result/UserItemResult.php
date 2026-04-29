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

namespace Bitrix24\SDK\Services\IM\User\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int $id
 * @property-read bool $active
 * @property-read string $name
 * @property-read string $first_name
 * @property-read string $last_name
 * @property-read string|null $work_position
 * @property-read string $color
 * @property-read string $avatar
 * @property-read string $avatar_hr
 * @property-read string $gender
 * @property-read string $birthday
 * @property-read bool $extranet
 * @property-read bool $network
 * @property-read bool $bot
 * @property-read bool $connector
 * @property-read string $external_auth_id
 * @property-read string $status
 * @property-read bool $idle
 * @property-read CarbonImmutable $last_activity_date
 * @property-read bool $mobile_last_date
 * @property-read bool $desktop_last_date
 * @property-read bool $absent
 * @property-read array $departments
 * @property-read array $phones
 * @property-read array|null $bot_data
 * @property-read string $type
 * @property-read string $website
 * @property-read string $email
 */
class UserItemResult extends AbstractAnnotatedItem
{
}
