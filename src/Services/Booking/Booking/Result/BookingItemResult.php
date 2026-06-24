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

namespace Bitrix24\SDK\Services\Booking\Booking\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int|null $id
 * @property-read string|null $name
 * @property-read string|null $description
 * @property-read array<string, mixed>|null $datePeriod
 * @property-read array<int, int>|null $resourceIds
 */
class BookingItemResult extends AbstractAnnotatedItem
{
}
