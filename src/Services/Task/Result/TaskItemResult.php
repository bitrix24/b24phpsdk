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
use Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNotFoundException;
use Carbon\CarbonImmutable;

/**
 * Class TaskItemResult
 *
 * @property-read int $id
 * @property-read int|null $parentId
 * @property-read string $title
 * @property-read string|null $description
 * @property-read string|null $mark
 * @property-read int|null $priority
 * @property-read int|null $status
 * @property-read bool|null $multitask
 * @property-read bool|null $notViewed
 * @property-read bool|null $replicate
 * @property-read int|null $groupId
 * @property-read int|null $stageId
 * @property-read int|null $sprintId
 * @property-read int|null $backlogId
 * @property-read int|null $createdBy
 * @property-read CarbonImmutable|null $createdDate
 * @property-read int|null $responsibleId
 * @property-read array|null $accomplices
 * @property-read array|null $auditors
 * @property-read int|null $changedBy
 * @property-read CarbonImmutable|null $changedDate
 * @property-read int|null $statusChangedBy
 * @property-read CarbonImmutable|null $statusChangedDate
 * @property-read int|null $closedBy
 * @property-read CarbonImmutable|null $closedDate
 * @property-read CarbonImmutable|null $activityDate
 * @property-read CarbonImmutable|null $dateStart
 * @property-read CarbonImmutable|null $deadline
 * @property-read CarbonImmutable|null $startDatePlan
 * @property-read CarbonImmutable|null $endDatePlan
 * @property-read string|null $guid
 * @property-read string|null $xmlId
 * @property-read int|null $commentsCount
 * @property-read int|null $serviceCommentsCount
 * @property-read int|null $newCommentsCount
 * @property-read bool|null $allowChangeDeadline
 * @property-read bool|null $allowTimeTracking
 * @property-read bool|null $taskControl
 * @property-read bool|null $addInReport
 * @property-read bool|null $forkedByTemplateId
 * @property-read int|null $timeEstimate
 * @property-read int|null $timeSpentInLogs
 * @property-read int|null $matchWorkTime
 * @property-read int|null $forumTopicId
 * @property-read int|null $forumId
 * @property-read string|null $siteId
 * @property-read bool|null $subordinate
 * @property-read bool|null $favorite
 * @property-read CarbonImmutable|null $exchangeModified
 * @property-read int|null $exchangeId
 * @property-read int|null $outlookVersion
 * @property-read CarbonImmutable|null $viewedDate
 * @property-read string|null $sorting
 * @property-read int|null $durationPlan
 * @property-read int|null $durationFact
 * @property-read array|null $checklist
 * @property-read string|null $durationType
 * @property-read bool|null $isMuted
 * @property-read bool|null $isPinned
 * @property-read bool|null $isPinnedInGroup
 * @property-read int|null $flowId
 * @property-read array|null $ufCrmTask
 * @property-read array|null $ufTaskWebdavFiles
 * @property-read int|null $ufMailMessage
 */
class TaskItemResult extends AbstractItem
{
    private const TASK_USERFIELD_PREFIX = 'UF_';

    /**
     *
     * @return mixed|null
     * @throws \Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNotFoundException
     */
    public function getUserfieldByFieldName(string $userfieldName): mixed
    {
        return $this->getKeyWithUserfieldByFieldName($userfieldName);
    }

    /**
     * get userfield by field name
     *
     * @param string $fieldName field name with uppercase letters
     *
     * @return mixed|null
     * @throws \Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNotFoundException
     */
    protected function getKeyWithUserfieldByFieldName(string $fieldName): mixed
    {
        if (!str_starts_with($fieldName, self::TASK_USERFIELD_PREFIX)) {
            $fieldName = self::TASK_USERFIELD_PREFIX . $fieldName;
        }

        $fieldName = $this->normalizeFieldKey($fieldName);
        if (!$this->isKeyExists($fieldName)) {
            throw new UserfieldNotFoundException(sprintf('Task userfield not found by field name %s', $fieldName));
        }

        return $this->$fieldName;
    }

    protected function normalizeFieldKey(string $field): string
    {
        $testStr = strtolower($field);
        $testArr = explode('_', $testStr);

        return  array_shift($testArr) . implode('', array_map('ucfirst', $testArr));
        ;
    }
}
