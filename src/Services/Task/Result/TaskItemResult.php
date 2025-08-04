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
 * Class TaskItemResult
 *
 * @property-read int $ID
 * @property-read int $PARENT_ID
 * @property-read string $TITLE
 * @property-read string|null $DESCRIPTION
 * @property-read string|null $MARK
 * @property-read int|null $PRIORITY
 * @property-read int|null $STATUS
 * @property-read bool|null $MULTITASK
 * @property-read bool|null $NOT_VIEWED
 * @property-read bool|null $REPLICATE
 * @property-read int|null $GROUP_ID
 * @property-read int|null $STAGE_ID
 * @property-read int|null $CREATED_BY
 * @property-read CarbonImmutable|null $CREATED_DATE
 * @property-read int|null $RESPONSIBLE_ID
 * @property-read array|null $ACCOMPLICES
 * @property-read array|null $AUDITORS
 * @property-read int|null $CHANGED_BY
 * @property-read CarbonImmutable|null $CHANGED_DATE
 * @property-read int|null $STATUS_CHANGED_BY
 * @property-read int|null $CLOSED_BY
 * @property-read CarbonImmutable|null $CLOSED_DATE
 * @property-read CarbonImmutable|null $DATE_START
 * @property-read CarbonImmutable|null $DEADLINE
 * @property-read CarbonImmutable|null $START_DATE_PLAN
 * @property-read CarbonImmutable|null $END_DATE_PLAN
 * @property-read string|null $GUID
 * @property-read string|null $XML_ID
 * @property-read int|null $COMMENTS_COUNT
 * @property-read int|null $NEW_COMMENTS_COUNT
 * @property-read bool|null $ALLOW_CHANGE_DEADLINE
 * @property-read bool|null $ALLOW_TIME_TRACKING
 * @property-read bool|null $TASK_CONTROL
 * @property-read bool|null $ADD_IN_REPORT
 * @property-read bool|null $FORKED_BY_TEMPLATE_ID
 * @property-read int|null $TIME_ESTIMATE
 * @property-read int|null $TIME_SPENT_IN_LOGS
 * @property-read int|null $MATCH_WORK_TIME
 * @property-read int|null $FORUM_TOPIC_ID
 * @property-read int|null $FORUM_ID
 * @property-read string|null $SITE_ID
 * @property-read bool|null $SUBORDINATE
 * @property-read bool|null $FAVORITE
 * @property-read CarbonImmutable|null $EXCHANGE_MODIFIED
 * @property-read int|null $EXCHANGE_ID
 * @property-read int|null $OUTLOOK_VERSION
 * @property-read CarbonImmutable|null $VIEWED_DATE
 * @property-read int|null $SORTING
 * @property-read int|null $DURATION_PLAN
 * @property-read int|null $DURATION_FACT
 * @property-read array|null $CHECKLIST
 * @property-read int|null $DURATION_TYPE
 * @property-read array|null $UF_CRM_TASK
 * @property-read array|null $UF_TASK_WEBDAV_FILES
 * @property-read array|null $UF_MAIL_MESSAGE
 * @property-read bool|null $IS_MUTED
 * @property-read bool|null $IS_PINNED
 * @property-read bool|null $IS_PINNED_IN_GROUP
 * @property-read int|null $SERVICE_COMMENTS_COUNT
 */
class TaskItemResult extends AbstractItem
{
}
