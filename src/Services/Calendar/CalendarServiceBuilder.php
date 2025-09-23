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

namespace Bitrix24\SDK\Services\Calendar;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Calendar\Service\Calendar;

/**
 * Class CalendarServiceBuilder
 *
 * @package Bitrix24\SDK\Services\Calendar
 */
#[ApiServiceBuilderMetadata(new Scope(['calendar']))]
class CalendarServiceBuilder extends AbstractServiceBuilder
{
    /**
     * Get Calendar service for calendar sections
     */
    public function calendar(): Calendar
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Calendar(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
