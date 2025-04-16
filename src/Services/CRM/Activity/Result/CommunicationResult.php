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

namespace Bitrix24\SDK\Services\CRM\Activity\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;

/**
 * @see https://apidocs.bitrix24.ru/api-reference/crm/timeline/activities/activity-base/crm-activity-communication-fields.html
 *
 * @property-read int $ID
 * @property-read int $ACTIVITY_ID
 * @property-read int $ENTITY_ID
 * @property-read int $ENTITY_TYPE_ID
 * @property-read string $TYPE
 * @property-read string $VALUE
 */
class CommunicationResult extends AbstractCrmItem
{
}
