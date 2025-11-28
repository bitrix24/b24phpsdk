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

namespace Bitrix24\SDK\Services\Task\Flow\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class FlowItemResult
 *
 * @property-read int $id
 * @property-read int $creatorId
 * @property-read int $ownerId
 * @property-read int $groupId
 * @property-read int $templateId
 * @property-read int $efficiency
 * @property-read bool $active
 * @property-read int $plannedCompletionTime
 * @property-read string $activity
 * @property-read string $name
 * @property-read string $description
 * @property-read string $distributionType
 * @property-read array|null $responsibleList
 * @property-read bool|null $demo
 * @property-read bool|null $responsibleCanChangeDeadline
 * @property-read bool|null $matchWorkTime
 * @property-read bool|null $taskControl
 * @property-read bool|null $notifyAtHalfTime
 * @property-read int|null $notifyOnQueueOverflow
 * @property-read int|null $notifyOnTasksInProgressOverflow
 * @property-read int|null $notifyWhenEfficiencyDecreases
 * @property-read array|null $taskCreators
 * @property-read array|null $team
 * @property-read bool|null $trialFeatureEnabled
 */
class FlowItemResult extends AbstractItem
{
}
