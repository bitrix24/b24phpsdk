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

/**
 * Class AccessItemResult
 *
 * @property-read int $userId
 * @property-read bool|null $read
 * @property-read bool|null $watch
 * @property-read bool|null $mute
 * @property-read bool|null $createResult
 * @property-read bool|null $edit
 * @property-read bool|null $remove
 * @property-read bool|null $complete
 * @property-read bool|null $approve
 * @property-read bool|null $disapprove
 * @property-read bool|null $start
 * @property-read bool|null $take
 * @property-read bool|null $delegate
 * @property-read bool|null $defer
 * @property-read bool|null $renew
 * @property-read bool|null $deadline
 * @property-read bool|null $datePlan
 * @property-read bool|null $changeDirector
 * @property-read bool|null $changeResponsible
 * @property-read bool|null $changeAccomplices
 * @property-read bool|null $pause
 * @property-read bool|null $timeTracking
 * @property-read bool|null $mark
 * @property-read bool|null $changeStatus
 * @property-read bool|null $reminder
 * @property-read bool|null $addAuditors
 * @property-read bool|null $elapsedTime
 * @property-read bool|null $favorite
 * @property-read bool|null $checklistAdd
 * @property-read bool|null $checklistEdit
 * @property-read bool|null $checklistSave
 * @property-read bool|null $checklistToggle
 * @property-read bool|null $automate
 * @property-read bool|null $resultEdit
 * @property-read bool|null $completeResult
 * @property-read bool|null $removeResult
 * @property-read bool|null $resultRead
 * @property-read bool|null $admin
 * @property-read bool|null $createSubtask
 * @property-read bool|null $copy
 * @property-read bool|null $saveAsTemplate
 * @property-read bool|null $attachFile
 * @property-read bool|null $detachFile
 * @property-read bool|null $detachParent
 * @property-read bool|null $createGanttDependence
 * @property-read bool|null $sort
 */
class AccessItemResult extends AbstractItem
{
    public function __construct(protected array $data, protected int $userId)
    {
        parent::__construct($data);
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
