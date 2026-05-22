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

namespace Bitrix24\SDK\Services\IM\Search\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $first_name
 * @property-read string|null $last_name
 * @property-read string|null $work_position
 * @property-read string $color
 * @property-read string $avatar
 * @property-read string $gender
 * @property-read string $birthday
 * @property-read bool $extranet
 * @property-read bool $network
 * @property-read bool $bot
 * @property-read bool $connector
 * @property-read string $external_auth_id
 * @property-read string|null $status
 * @property-read bool $idle
 * @property-read string $last_activity_date
 * @property-read string $mobile_last_date
 * @property-read array $departments
 * @property-read bool $absent
 * @property-read array|null $phones
 */
class SearchUserItemResult extends AbstractAnnotatedItem
{
}
