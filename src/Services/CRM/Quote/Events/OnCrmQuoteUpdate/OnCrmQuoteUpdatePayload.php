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

namespace Bitrix24\SDK\Services\CRM\Quote\Events\OnCrmQuoteUpdate;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read  array{
 *	 	ID: int,
 * 		RECURRING_DEAL_ID: int,
 *	 } $FIELDS
 */
class OnCrmQuoteUpdatePayload extends AbstractItem
{
}
