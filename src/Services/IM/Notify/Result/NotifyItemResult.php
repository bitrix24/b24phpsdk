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

namespace Bitrix24\SDK\Services\IM\Notify\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int $id
 * @property-read int $chat_id
 * @property-read int $author_id
 * @property-read CarbonImmutable $date
 * @property-read int $notify_type
 * @property-read string $notify_module
 * @property-read string $notify_event
 * @property-read string $notify_tag
 * @property-read string $notify_sub_tag
 * @property-read string $notify_title
 * @property-read string $setting_name
 * @property-read string $text
 * @property-read string $notify_read
 * @property-read array|null $params
 */
class NotifyItemResult extends AbstractAnnotatedItem
{
}
