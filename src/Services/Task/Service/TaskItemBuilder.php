<?php

namespace Bitrix24\SDK\Services\Task\Service;

use Bitrix24\SDK\Services\AbstractItemBuilder;
use Bitrix24\SDK\Services\Task\Result\TaskItemResult;
use Carbon\CarbonInterface;

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

    public function title(string $title): self
    {
        $this->fields['title'] = $title;
        return $this;
    }

    public function description(string $description): self
    {
        $this->fields['description'] = $description;
        return $this;
    }

    public function deadline(CarbonInterface $deadline): self
    {
        $this->fields['deadline'] = $deadline->format(DATE_ATOM);
        return $this;
    }

    public function needsControl(bool $isNeedsControl = false): self
    {
        $this->fields['needsControl'] = $isNeedsControl ? 'Y' : 'N';
        return $this;
    }

    public function startPlan(CarbonInterface $startPlan): self
    {
        $this->fields['startPlan'] = $startPlan->format(DATE_ATOM);
        return $this;
    }

    public function endPlan(CarbonInterface $endPlan): self
    {
        $this->fields['endPlan'] = $endPlan->format(DATE_ATOM);
        return $this;
    }

    public function groupId(int $groupId): self
    {
        $this->fields['groupId'] = $groupId;
        return $this;
    }

    public function stageId(int $stageId): self
    {
        $this->fields['stageId'] = $stageId;
        return $this;
    }

    public function creatorId(int $creatorId): self
    {
        $this->fields['creatorId'] = $creatorId;
        return $this;
    }

    public function responsibleId(int $responsibleId): self
    {
        $this->fields['responsibleId'] = $responsibleId;
        return $this;
    }
}
