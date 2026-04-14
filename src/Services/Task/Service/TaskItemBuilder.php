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

namespace Bitrix24\SDK\Services\Task\Service;

use Bitrix24\SDK\Services\AbstractItemBuilder;
use Bitrix24\SDK\Services\Task\Result\TaskItemResult;
use Carbon\CarbonInterface;

// Methods for deadline / startPlan / endPlan accept CarbonInterface and serialize to DATE_ATOM.
// The needsControl method maps bool to the 'Y'/'N' string expected by the API.
// All other methods are generated from the OpenAPI schema (docs/open-api/openapi.json).
// To regenerate the full scaffold, run: php bin/console b24-dev:generate-item-builder /tasks.task.add
class TaskItemBuilder extends AbstractItemBuilder
{
    public static function createFromTask(TaskItemResult $taskItemResult): self
    {
        return new self(
            $taskItemResult->title,
            (int)$taskItemResult->createdBy,
            $taskItemResult->responsibleId
        );
    }

    public function __construct(
        string $title,
        int $creatorId,
        int $responsibleId
    ) {
        $this->fields['title'] = $title;
        $this->fields['creatorId'] = $creatorId;
        $this->fields['responsibleId'] = $responsibleId;
    }

    public function activity(string $activity): self
    {
        $this->fields['activity'] = $activity;
        return $this;
    }

    public function actualDuration(int $actualDuration): self
    {
        $this->fields['actualDuration'] = $actualDuration;
        return $this;
    }

    public function addInReport(bool $addInReport): self
    {
        $this->fields['addInReport'] = $addInReport;
        return $this;
    }

    public function allowsChangeDatePlan(bool $allowsChangeDatePlan): self
    {
        $this->fields['allowsChangeDatePlan'] = $allowsChangeDatePlan;
        return $this;
    }

    public function allowsChangeDeadline(bool $allowsChangeDeadline): self
    {
        $this->fields['allowsChangeDeadline'] = $allowsChangeDeadline;
        return $this;
    }

    public function allowsTimeTracking(bool $allowsTimeTracking): self
    {
        $this->fields['allowsTimeTracking'] = $allowsTimeTracking;
        return $this;
    }

    public function archiveLink(string $archiveLink): self
    {
        $this->fields['archiveLink'] = $archiveLink;
        return $this;
    }

    public function autocompleteSubTasks(bool $autocompleteSubTasks): self
    {
        $this->fields['autocompleteSubTasks'] = $autocompleteSubTasks;
        return $this;
    }

    public function changed(string $changed): self
    {
        $this->fields['changed'] = $changed;
        return $this;
    }

    public function changedById(int $changedById): self
    {
        $this->fields['changedById'] = $changedById;
        return $this;
    }

    public function chatId(int $chatId): self
    {
        $this->fields['chatId'] = $chatId;
        return $this;
    }

    public function checklist(array $checklist): self
    {
        $this->fields['checklist'] = $checklist;
        return $this;
    }

    public function closed(string $closed): self
    {
        $this->fields['closed'] = $closed;
        return $this;
    }

    public function closedById(int $closedById): self
    {
        $this->fields['closedById'] = $closedById;
        return $this;
    }

    public function containsChecklist(bool $containsChecklist): self
    {
        $this->fields['containsChecklist'] = $containsChecklist;
        return $this;
    }

    public function containsGanttLinks(bool $containsGanttLinks): self
    {
        $this->fields['containsGanttLinks'] = $containsGanttLinks;
        return $this;
    }

    public function containsPlacements(bool $containsPlacements): self
    {
        $this->fields['containsPlacements'] = $containsPlacements;
        return $this;
    }

    public function containsRelatedTasks(bool $containsRelatedTasks): self
    {
        $this->fields['containsRelatedTasks'] = $containsRelatedTasks;
        return $this;
    }

    public function containsResults(bool $containsResults): self
    {
        $this->fields['containsResults'] = $containsResults;
        return $this;
    }

    public function containsSubTasks(bool $containsSubTasks): self
    {
        $this->fields['containsSubTasks'] = $containsSubTasks;
        return $this;
    }

    public function created(string $created): self
    {
        $this->fields['created'] = $created;
        return $this;
    }

    public function creatorId(int $creatorId): self
    {
        $this->fields['creatorId'] = $creatorId;
        return $this;
    }

    public function crmItemIds(array $crmItemIds): self
    {
        $this->fields['crmItemIds'] = $crmItemIds;
        return $this;
    }

    public function deadline(CarbonInterface $deadline): self
    {
        $this->fields['deadline'] = $deadline->format(DATE_ATOM);
        return $this;
    }

    public function deadlineCount(int $deadlineCount): self
    {
        $this->fields['deadlineCount'] = $deadlineCount;
        return $this;
    }

    public function declineReason(string $declineReason): self
    {
        $this->fields['declineReason'] = $declineReason;
        return $this;
    }

    public function dependsOn(array $dependsOn): self
    {
        $this->fields['dependsOn'] = $dependsOn;
        return $this;
    }

    public function description(string $description): self
    {
        $this->fields['description'] = $description;
        return $this;
    }

    public function durationType(string $durationType): self
    {
        $this->fields['durationType'] = $durationType;
        return $this;
    }

    public function emailId(int $emailId): self
    {
        $this->fields['emailId'] = $emailId;
        return $this;
    }

    public function endPlan(CarbonInterface $endPlan): self
    {
        $this->fields['endPlan'] = $endPlan->format(DATE_ATOM);
        return $this;
    }

    public function epicId(int $epicId): self
    {
        $this->fields['epicId'] = $epicId;
        return $this;
    }

    public function estimatedTime(int $estimatedTime): self
    {
        $this->fields['estimatedTime'] = $estimatedTime;
        return $this;
    }

    public function exchangeId(string $exchangeId): self
    {
        $this->fields['exchangeId'] = $exchangeId;
        return $this;
    }

    public function exchangeModified(string $exchangeModified): self
    {
        $this->fields['exchangeModified'] = $exchangeModified;
        return $this;
    }

    public function fileIds(array $fileIds): self
    {
        $this->fields['fileIds'] = $fileIds;
        return $this;
    }

    public function flowId(int $flowId): self
    {
        $this->fields['flowId'] = $flowId;
        return $this;
    }

    public function forkedByTemplateId(int $forkedByTemplateId): self
    {
        $this->fields['forkedByTemplateId'] = $forkedByTemplateId;
        return $this;
    }

    public function forumTopicId(int $forumTopicId): self
    {
        $this->fields['forumTopicId'] = $forumTopicId;
        return $this;
    }

    public function groupId(int $groupId): self
    {
        $this->fields['groupId'] = $groupId;
        return $this;
    }

    public function guid(string $guid): self
    {
        $this->fields['guid'] = $guid;
        return $this;
    }

    public function id(int $id): self
    {
        $this->fields['id'] = $id;
        return $this;
    }

    public function inFavorite(array $inFavorite): self
    {
        $this->fields['inFavorite'] = $inFavorite;
        return $this;
    }

    public function inGroupPin(array $inGroupPin): self
    {
        $this->fields['inGroupPin'] = $inGroupPin;
        return $this;
    }

    public function inMute(array $inMute): self
    {
        $this->fields['inMute'] = $inMute;
        return $this;
    }

    public function inPin(array $inPin): self
    {
        $this->fields['inPin'] = $inPin;
        return $this;
    }

    public function isMultitask(bool $isMultitask): self
    {
        $this->fields['isMultitask'] = $isMultitask;
        return $this;
    }

    public function link(string $link): self
    {
        $this->fields['link'] = $link;
        return $this;
    }

    public function mark(string $mark): self
    {
        $this->fields['mark'] = $mark;
        return $this;
    }

    public function matchesSubTasksTime(bool $matchesSubTasksTime): self
    {
        $this->fields['matchesSubTasksTime'] = $matchesSubTasksTime;
        return $this;
    }

    public function matchesWorkTime(bool $matchesWorkTime): self
    {
        $this->fields['matchesWorkTime'] = $matchesWorkTime;
        return $this;
    }

    public function maxDeadlineChangeDate(string $maxDeadlineChangeDate): self
    {
        $this->fields['maxDeadlineChangeDate'] = $maxDeadlineChangeDate;
        return $this;
    }

    public function maxDeadlineChanges(int $maxDeadlineChanges): self
    {
        $this->fields['maxDeadlineChanges'] = $maxDeadlineChanges;
        return $this;
    }

    public function needsControl(bool $isNeedsControl = false): self
    {
        $this->fields['needsControl'] = $isNeedsControl ? 'Y' : 'N';
        return $this;
    }

    public function numberOfReminders(int $numberOfReminders): self
    {
        $this->fields['numberOfReminders'] = $numberOfReminders;
        return $this;
    }

    public function outlookVersion(int $outlookVersion): self
    {
        $this->fields['outlookVersion'] = $outlookVersion;
        return $this;
    }

    public function parentId(int $parentId): self
    {
        $this->fields['parentId'] = $parentId;
        return $this;
    }

    public function plannedDuration(int $plannedDuration): self
    {
        $this->fields['plannedDuration'] = $plannedDuration;
        return $this;
    }

    public function priority(string $priority): self
    {
        $this->fields['priority'] = $priority;
        return $this;
    }

    public function reminders(array $reminders): self
    {
        $this->fields['reminders'] = $reminders;
        return $this;
    }

    public function replicate(bool $replicate): self
    {
        $this->fields['replicate'] = $replicate;
        return $this;
    }

    public function requireDeadlineChangeReason(bool $requireDeadlineChangeReason): self
    {
        $this->fields['requireDeadlineChangeReason'] = $requireDeadlineChangeReason;
        return $this;
    }

    public function requireResult(bool $requireResult): self
    {
        $this->fields['requireResult'] = $requireResult;
        return $this;
    }

    public function responsibleId(int $responsibleId): self
    {
        $this->fields['responsibleId'] = $responsibleId;
        return $this;
    }

    public function rights(array $rights): self
    {
        $this->fields['rights'] = $rights;
        return $this;
    }

    public function scenarios(array $scenarios): self
    {
        $this->fields['scenarios'] = $scenarios;
        return $this;
    }

    public function siteId(string $siteId): self
    {
        $this->fields['siteId'] = $siteId;
        return $this;
    }

    public function stageId(int $stageId): self
    {
        $this->fields['stageId'] = $stageId;
        return $this;
    }

    public function startPlan(CarbonInterface $startPlan): self
    {
        $this->fields['startPlan'] = $startPlan->format(DATE_ATOM);
        return $this;
    }

    public function started(string $started): self
    {
        $this->fields['started'] = $started;
        return $this;
    }

    public function status(string $status): self
    {
        $this->fields['status'] = $status;
        return $this;
    }

    public function statusChanged(string $statusChanged): self
    {
        $this->fields['statusChanged'] = $statusChanged;
        return $this;
    }

    public function statusChangedById(int $statusChangedById): self
    {
        $this->fields['statusChangedById'] = $statusChangedById;
        return $this;
    }

    public function storyPoints(int $storyPoints): self
    {
        $this->fields['storyPoints'] = $storyPoints;
        return $this;
    }

    public function title(string $title): self
    {
        $this->fields['title'] = $title;
        return $this;
    }

    public function xmlId(string $xmlId): self
    {
        $this->fields['xmlId'] = $xmlId;
        return $this;
    }
}
