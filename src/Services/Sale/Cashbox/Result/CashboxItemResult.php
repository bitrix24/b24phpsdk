<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Cashbox\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class CashboxItemResult
 *
 * @property-read int|null $ID
 * @property-read string|null $NAME
 * @property-read string|null $REST_CODE
 * @property-read string|null $EMAIL
 * @property-read string|null $OFD
 * @property-read array|null $OFD_SETTINGS
 * @property-read string|null $NUMBER_KKM
 * @property-read string|null $ACTIVE
 * @property-read int|null $SORT
 * @property-read string|null $USE_OFFLINE
 * @property-read array|null $SETTINGS
 */
class CashboxItemResult extends AbstractItem
{
}