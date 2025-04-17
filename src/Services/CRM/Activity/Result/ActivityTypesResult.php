<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Gleb Starikov <gleb.starikov1998@mail.ru>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Activity\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

/**
 * @see https://apidocs.bitrix24.com/api-reference/crm/timeline/activities/types/crm-activity-type-add.html
 *
 * @property-read string $TYPE_ID
 * @property-read string $NAME
 * @property-read string $IS_CONFIGURABLE_TYPE
 * @property-read int $ICON_ID
 */
class ActivityTypesResult extends AbstractResult
{
    /**
     * @return \Bitrix24\SDK\Services\CRM\Activity\Result\ActivityTypeResult[]
     * @throws BaseException
     */
    public function getActivityTypes(): array
    {
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $res[] = new ActivityTypeResult($item);
        }

        return $res;
    }
}
