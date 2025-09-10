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

namespace Bitrix24\SDK\Services\Sale\BasketProperty\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class BasketPropertyItemResult
 *
 * @property-read int|null $id
 * @property-read int|null $basketId
 * @property-read string|null $name
 * @property-read string|null $value
 * @property-read string|null $code
 * @property-read int|null $sort
 * @property-read string|null $xmlId
 */
class BasketPropertyItemResult extends AbstractItem
{
}
