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

namespace Bitrix24\SDK\Services\CRM\Deal\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;

/**
 * Class DealRecurringItemResult
 *
 * @property int             $ID
 * @property int             $DEAL_ID
 * @property int             $BASED_ID
 * @property bool            $ACTIVE
 * @property CarbonImmutable $NEXT_EXECUTION
 * @property CarbonImmutable $LAST_EXECUTION
 * @property int             $COUNTER_REPEAT
 * @property CarbonImmutable $START_DATE
 * @property string          $CATEGORY_ID
 * @property string          $IS_LIMIT
 * @property int             $LIMIT_REPEAT
 * @property CarbonImmutable $LIMIT_DATE
 * @property array{
 *		MODE?: string,
 *      MULTIPLE_TYPE?: string,
 *      MULTIPLE_INTERVAL?: string,
 *      SINGLE_BEFORE_START_DATE_TYPE?: string,
 *      SINGLE_BEFORE_START_DATE_VALUE?: string,
 *      OFFSET_BEGINDATE_TYPE?: string,
 *      OFFSET_BEGINDATE_VALUE?: string,
 *      OFFSET_CLOSEDATE_TYPE?: string,
 *      OFFSET_CLOSEDATE_VALUE?: string,
 * } $PARAMS
 */
class DealRecurringItemResult extends AbstractCrmItem
{
}
