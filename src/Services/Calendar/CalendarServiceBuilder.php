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
    public function calendar(): Service\Calendar
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Service\Calendar(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get Event service for calendar events
     */
    public function event(): Event\Service\Event
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $batch = new Event\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Event\Service\Event(
                new Event\Service\Batch($batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get Resource service for calendar resources
     */
    public function resource(): Resource\Service\Resource
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Resource\Service\Resource(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
