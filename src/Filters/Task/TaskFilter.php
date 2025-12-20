<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Filters\Task;

use Bitrix24\SDK\Filters\Core\AbstractFilterBuilder;
use Bitrix24\SDK\Filters\Core\BoolFieldConditionBuilder;
use Bitrix24\SDK\Filters\Core\DateFieldConditionBuilder;
use Bitrix24\SDK\Filters\Core\FieldConditionBuilder;
use Bitrix24\SDK\Filters\Core\IntFieldConditionBuilder;
use Bitrix24\SDK\Filters\Core\StringFieldConditionBuilder;

/**
 * Class TaskFilter
 *
 * Type-safe filter builder for Task entity with support for REST 3.0 filtering.
 *
 * @package Bitrix24\SDK\Filters\Task
 */
class TaskFilter extends AbstractFilterBuilder
{
    // Identifiers

    public function id(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('id', $this);
    }

    public function parentId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('parentId', $this);
    }

    public function groupId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('groupId', $this);
    }

    public function stageId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('stageId', $this);
    }

    public function forumTopicId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('forumTopicId', $this);
    }

    public function sprintId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('sprintId', $this);
    }

    // Text fields

    public function title(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('title', $this);
    }

    public function description(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('description', $this);
    }

    public function xmlId(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('xmlId', $this);
    }

    public function guid(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('guid', $this);
    }

    // Status fields

    public function status(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('status', $this);
    }

    public function priority(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('priority', $this);
    }

    public function mark(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('mark', $this);
    }

    // People fields

    public function createdBy(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('createdBy', $this);
    }

    public function responsibleId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('responsibleId', $this);
    }

    public function changedBy(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('changedBy', $this);
    }

    public function closedBy(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('closedBy', $this);
    }

    // Date fields

    public function createdDate(): DateFieldConditionBuilder
    {
        return new DateFieldConditionBuilder('createdDate', $this);
    }

    public function changedDate(): DateFieldConditionBuilder
    {
        return new DateFieldConditionBuilder('changedDate', $this);
    }

    public function closedDate(): DateFieldConditionBuilder
    {
        return new DateFieldConditionBuilder('closedDate', $this);
    }

    public function deadline(): DateFieldConditionBuilder
    {
        return new DateFieldConditionBuilder('deadline', $this);
    }

    public function dateStart(): DateFieldConditionBuilder
    {
        return new DateFieldConditionBuilder('dateStart', $this);
    }

    public function startDatePlan(): DateFieldConditionBuilder
    {
        return new DateFieldConditionBuilder('startDatePlan', $this);
    }

    public function endDatePlan(): DateFieldConditionBuilder
    {
        return new DateFieldConditionBuilder('endDatePlan', $this);
    }

    // Boolean fields

    public function multitask(): BoolFieldConditionBuilder
    {
        return new BoolFieldConditionBuilder('multitask', $this);
    }

    public function taskControl(): BoolFieldConditionBuilder
    {
        return new BoolFieldConditionBuilder('taskControl', $this);
    }

    public function subordinate(): BoolFieldConditionBuilder
    {
        return new BoolFieldConditionBuilder('subordinate', $this);
    }

    public function favorite(): BoolFieldConditionBuilder
    {
        return new BoolFieldConditionBuilder('favorite', $this);
    }

    public function isMuted(): BoolFieldConditionBuilder
    {
        return new BoolFieldConditionBuilder('isMuted', $this);
    }

    // Number fields

    public function timeEstimate(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('timeEstimate', $this);
    }

    public function commentsCount(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('commentsCount', $this);
    }

    public function durationPlan(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('durationPlan', $this);
    }

    // User fields

    /**
     * Access user field with UF_ prefix
     *
     * @param string $fieldName User field name (UF_ prefix is added automatically if missing)
     * @return FieldConditionBuilder
     */
    public function userField(string $fieldName): FieldConditionBuilder
    {
        if (!str_starts_with($fieldName, 'UF_')) {
            $fieldName = 'UF_' . $fieldName;
        }

        return new FieldConditionBuilder($fieldName, $this);
    }
}
