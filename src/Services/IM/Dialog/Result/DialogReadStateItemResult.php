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

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read string|null $dialogId
 * @property-read int|null $chatId
 * @property-read int|null $lastId
 * @property-read int|null $counter
 */
class DialogReadStateItemResult extends AbstractItem
{
}
