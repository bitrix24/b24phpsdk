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
 * Class CalendarUserSettingsResult
 * Represents the result of a get calendar user settings operation.
 */
class CalendarUserSettingsResult extends AbstractResult
{
    /**
     * Returns the calendar user settings array
     */
    public function getSettings(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult();
    }
}
