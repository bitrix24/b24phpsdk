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
use Carbon\CarbonImmutable;

/**
 * Class HistoryItemResult
 *
 * @property-read int $id
 * @property-read CarbonImmutable|null $createdDate
 * @property-read string|null $field
 * @property-read array $value
 * @property-read array $user
 */
class HistoryItemResult extends AbstractItem
{
}
