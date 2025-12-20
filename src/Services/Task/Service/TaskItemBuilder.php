<?php

namespace Bitrix24\SDK\Services\Task\Service;

use Bitrix24\SDK\Services\AbstractItemBuilder;

class TaskItemBuilder extends AbstractItemBuilder
{
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
