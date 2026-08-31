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

namespace Bitrix24\SDK\Services\Booking\WaitlistExternalData\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read string|null $entityTypeId
 * @property-read string|null $moduleId
 * @property-read string|null $value
 */
class WaitlistExternalDataItemResult extends AbstractItem
{
}
