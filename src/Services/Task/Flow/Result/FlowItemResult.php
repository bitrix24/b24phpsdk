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

namespace Bitrix24\SDK\Services\Task\Flow\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class FlowItemResult
 *
 * @property-read int $ID
 * @property-read string $TITLE
 * @property-read int $SORT
 * @property-read string|null $COLOR
 * @property-read string|null $SYSTEM_TYPE
 * @property-read int $ENTITY_ID
 * @property-read string $ENTITY_TYPE
 * @property-read array|null $ADDITIONAL_FILTER
 * @property-read array|null $TO_UPDATE
 * @property-read null $TO_UPDATE_ACCESS
 */
class FlowItemResult extends AbstractItem
{
}
