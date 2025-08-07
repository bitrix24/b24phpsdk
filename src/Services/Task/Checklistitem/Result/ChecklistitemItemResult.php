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

namespace Bitrix24\SDK\Services\Task\Checklistitem\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class ChecklistitemItemResult
 *
 * @property-read int $ID
 * @property-read string $TITLE
 * @property-read int|null $CREATED_BY
 * @property-read int|null $TOGGLED_BY
 * @property-read CarbonImmutable|null $TOGGLED_DATE
 * @property-read int|null $SORT_INDEX
 * @property-read bool|null $IS_COMPLETE
 */
class ChecklistitemItemResult extends AbstractItem
{
}
