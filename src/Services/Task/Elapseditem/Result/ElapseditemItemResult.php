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

namespace Bitrix24\SDK\Services\Task\Elapseditem\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class ElapseditemItemResult
 *
 * @property-read int $ID
 * @property-read int $TASK_ID
 * @property-read int|null $USER_ID
 * @property-read string $COMMENT_TEXT
 * @property-read int|null $SECONDS
 * @property-read int|null $MINUTES
 * @property-read int|null $SOURCE
 * @property-read CarbonImmutable|null $CREATED_DATE
 * @property-read CarbonImmutable|null $DATE_START
 * @property-read CarbonImmutable|null $DATE_STOP
 */
class ElapseditemItemResult extends AbstractItem
{
}
