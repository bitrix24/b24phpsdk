<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class CounterItemResult
 *
 * @property-read string $key
 * @property-read int|null $counter
 * @property-read int|null $code
 */
class CounterItemResult extends AbstractItem
{
}
