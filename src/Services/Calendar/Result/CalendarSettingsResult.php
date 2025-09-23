<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Calendar\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class CalendarSettingsResult
 * Represents the result of a get calendar settings operation.
 */
class CalendarSettingsResult extends AbstractResult
{
    /**
     * Returns the calendar settings as CalendarSettingsItemResult
     */
    public function getSettings(): CalendarSettingsItemResult
    {
        return new CalendarSettingsItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
