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

namespace Bitrix24\SDK\Services\Paysystem\Handler\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class HandlerItemResult
 *
 * @property-read int $ID Payment system handler identifier
 * @property-read string $NAME Name of the REST handler
 * @property-read string $CODE Code of the REST handler
 * @property-read int $SORT Sorting order
 * @property-read array $SETTINGS Handler settings containing currency, client type, form/checkout/iframe data, and codes
 */
class HandlerItemResult extends AbstractItem
{
}
