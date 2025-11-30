<?php

namespace Bitrix24\SDK\Services\Task\Service;

class TaskItemSelectBuilder
{
    protected array $select = [];

    public function __construct()
    {
        $this->select[] = 'id';
    }

    public function title(): self
    {
        $this->select[] = 'title';
        return $this;
    }

    public function description(): self
    {
        $this->select[] = 'description';
        return $this;
    }

    public function creatorId(): self
    {
        $this->select[] = 'creatorId';
        return $this;
    }

    public function creator(): self
    {
        $this->select[] = 'creator';
        return $this;
    }

    public function created(): self
    {
        $this->select[] = 'created';
        return $this;
    }

    public function chat(): self
    {
        $this->select = array_merge($this->select, ['chat.id', 'chat.entityId']);
        return $this;
    }

    public function buildSelect(): array
    {
        return $this->select;
    }
}
