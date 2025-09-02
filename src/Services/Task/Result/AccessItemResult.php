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
 * Class AccessItemResult
 *
 * @property-read int $userId
 * @property-read bool|null $ACCEPT
 * @property-read bool|null $DECLINE
 * @property-read bool|null $COMPLETE
 * @property-read bool|null $APPROVE
 * @property-read bool|null $DISAPPROVE
 * @property-read bool|null $START
 * @property-read bool|null $PAUSE
 * @property-read bool|null $DELEGATE
 * @property-read bool|null $REMOVE
 * @property-read bool|null $EDIT
 * @property-read bool|null $DEFER
 * @property-read bool|null $RENEW
 * @property-read bool|null $CREATE
 * @property-read bool|null $CREATE
 * @property-read bool|null $CHANGE_DEADLINE
 * @property-read bool|null $CHECKLIST_ADD_ITEMS
 * @property-read bool|null $ADD_FAVORITE
 * @property-read bool|null $DELETE_FAVORITE
 * @property-read bool|null $RATE
 * @property-read bool|null $TAKE
 * @property-read bool|null $EDIT_ORIGINATOR
 * @property-read bool|null $CHECKLIST_REORDER
 * @property-read bool|null $ELAPSEDTIME_ADD
 * @property-read bool|null $DAYPLAN_TIMER_TOGGLE
 * @property-read bool|null $EDIT_PLAN
 * @property-read bool|null $CHECKLIST_ADD
 * @property-read bool|null $FAVORITE_ADD
 * @property-read bool|null $FAVORITE_DELETE
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
