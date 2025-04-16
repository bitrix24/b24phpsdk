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
 * @see https://apidocs.bitrix24.com/api-reference/crm/timeline/activities/types/crm-activity-type-add.html
 *
 * @property-read string $TYPE_ID
 * @property-read string $NAME
 * @property-read string $IS_CONFIGURABLE_TYPE
 * @property-read string $ICON_ID
 */
class ActivityTypeResult extends AbstractCrmItem
{
    public function getData(): array
    {
        return $this->data;
    }
}
