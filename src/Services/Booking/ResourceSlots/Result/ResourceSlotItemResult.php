<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Veronica Akhmetova <264936994+fatestr1ngs@users.noreply.github.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Booking\ResourceSlots\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int|null $id
 * @property-read int|null $from
 * @property-read int|null $to
 * @property-read string|null $timezone
 * @property-read array<int, string>|null $weekDays
 * @property-read int|null $slotSize
 */
class ResourceSlotItemResult extends AbstractAnnotatedItem
{
}
