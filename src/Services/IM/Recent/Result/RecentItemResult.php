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

namespace Bitrix24\SDK\Services\IM\Recent\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read string $id
 * @property-read string $type
 * @property-read string $avatar
 * @property-read string $color
 * @property-read string $title
 * @property-read int $counter
 * @property-read bool $unread
 * @property-read bool $pinned
 * @property-read int $user_id
 * @property-read int $chat_id
 * @property-read array $message
 * @property-read CarbonImmutable|null $date_message
 */
class RecentItemResult extends AbstractAnnotatedItem
{
}
