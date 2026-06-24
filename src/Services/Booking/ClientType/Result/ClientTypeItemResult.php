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

namespace Bitrix24\SDK\Services\Booking\ClientType\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * Client type item for booking.v1.clienttype.list.
 *
 * The live API currently exposes the CRM module name and client code.
 *
 * @property-read string|null $code
 * @property-read string|null $module
 */
class ClientTypeItemResult extends AbstractAnnotatedItem
{
}
