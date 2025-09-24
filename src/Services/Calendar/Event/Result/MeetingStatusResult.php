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

namespace Bitrix24\SDK\Services\Calendar\Event\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Meeting status result
 */
class MeetingStatusResult extends AbstractResult
{
    /**
     * Get meeting status
     */
    public function getMeetingStatus(): string
    {
        return (string)$this->getCoreResponse()->getResponseData()->getResult()[0];
    }
}
