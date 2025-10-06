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

namespace Bitrix24\SDK\Services\Sale\CashboxHandler\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class CashboxHandlerItemResult
 *
 * @property-read int|null $ID
 * @property-read string|null $NAME
 * @property-read string|null $CODE
 * @property-read int|null $SORT
 * @property-read array|null $SETTINGS
 * @property-read string|null $SETTINGS.PRINT_URL
 * @property-read string|null $SETTINGS.CHECK_URL
 * @property-read string|null $SETTINGS.HTTP_VERSION
 * @property-read array|null $SETTINGS.CONFIG
 * @property-read string|null $SETTINGS.SUPPORTS_FFD105
 */
class CashboxHandlerItemResult extends AbstractItem
{
}
