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

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractItem;
use Bitrix24\SDK\Services\Task\Service\TaskItemBuilder;
use Bitrix24\SDK\Services\Task\Service\TaskItemSelectBuilder;

/**
 * Class TaskItemResult
 *
 * @property-read int $id
 * @property-read string $title
 * @property-read string|null $description
 * @property-read int|null $creatorId
 * @property-read array|null $creator
 * @property-read string|null $created
 * @property-read int|null $responsibleId
 * @property-read array|null $responsible
 * @property-read string|null $deadline
 * @property-read bool|null $needsControl
 * @property-read string|null $startPlan
 * @property-read string|null $endPlan
 * @property-read array|null $fileIds
 * @property-read array|null $checklist
 * @property-read int|null $groupId
 * @property-read array|null $group
 * @property-read int|null $stageId
 * @property-read array|null $stage
 * @property-read int|null $epicId
 * @property-read int|null $storyPoints
 * @property-read int|null $flowId
 * @property-read array|null $flow
 * @property-read string|null $priority
 * @property-read string|null $status
 * @property-read string|null $statusChanged
 * @property-read array|null $accomplices
 * @property-read array|null $auditors
 * @property-read int|null $parentId
 * @property-read array|null $parent
 * @property-read bool|null $containsChecklist
 * @property-read bool|null $containsSubTasks
 * @property-read bool|null $containsRelatedTasks
 * @property-read bool|null $containsGanttLinks
 * @property-read bool|null $containsPlacements
 * @property-read bool|null $containsResults
 * @property-read int|null $numberOfReminders
 * @property-read int|null $chatId
 * @property-read array|null $chat
 * @property-read int|null $plannedDuration
 * @property-read int|null $actualDuration
 * @property-read string|null $durationType
 * @property-read string|null $started
 * @property-read int|null $estimatedTime
 * @property-read bool|null $replicate
 * @property-read string|null $changed
 * @property-read int|null $changedById
 * @property-read array|null $changedBy
 * @property-read int|null $statusChangedById
 * @property-read array|null $statusChangedBy
 * @property-read int|null $closedById
 * @property-read array|null $closedBy
 * @property-read string|null $closed
 * @property-read string|null $activity
 * @property-read string|null $guid
 * @property-read string|null $xmlId
 * @property-read string|null $exchangeId
 * @property-read string|null $exchangeModified
 * @property-read int|null $outlookVersion
 * @property-read string|null $mark
 * @property-read bool|null $allowsChangeDeadline
 * @property-read bool|null $allowsTimeTracking
 * @property-read bool|null $matchesWorkTime
 * @property-read bool|null $addInReport
 * @property-read bool|null $isMultitask
 * @property-read string|null $siteId
 * @property-read int|null $forkedByTemplateId
 * @property-read array|null $forkedByTemplate
 * @property-read int|null $deadlineCount
 * @property-read string|null $declineReason
 * @property-read int|null $forumTopicId
 * @property-read array|null $tags
 * @property-read string|null $link
 * @property-read array|null $userFields
 * @property-read array|null $rights
 * @property-read string|null $archiveLink
 * @property-read array|null $crmItemIds
 * @property-read array|null $reminders
 * @property-read array|null $elapsedTime
 * @property-read bool|null $requireResult
 * @property-read bool|null $matchesSubTasksTime
 * @property-read bool|null $autocompleteSubTasks
 * @property-read bool|null $allowsChangeDatePlan
 * @property-read int|null $emailId
 * @property-read array|null $email
 * @property-read string|null $maxDeadlineChangeDate
 * @property-read int|null $maxDeadlineChanges
 * @property-read bool|null $requireDeadlineChangeReason
 * @property-read array|null $inFavorite
 * @property-read array|null $inPin
 * @property-read array|null $inGroupPin
 * @property-read array|null $inMute
 * @property-read array|null $source
 * @property-read array|null $dependsOn
 * @property-read array|null $scenarios
 * @property-read int|null $createdBy
 * @property-read array|null $ufTaskWebdavFiles
 */
#[OpenApiEntity(
    entityKey:     'bitrix.tasks.taskdto',
    selectBuilder: TaskItemSelectBuilder::class,
    itemBuilder:   TaskItemBuilder::class,
)]
class TaskItemResult extends AbstractItem
{
    private const string USERFIELD_PREFIX = 'UF_';

    public function getUserfieldByFieldName(string $fieldName): mixed
    {
        return $this->data[self::USERFIELD_PREFIX . $fieldName] ?? null;
    }
}
