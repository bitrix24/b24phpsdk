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

namespace Bitrix24\SDK\Services\Calendar\Resource\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class BookingsResult extends AbstractResult
{
    /**
     * @return ResourceItemResult[]
     * @throws BaseException
     */
    public function getBookings(): array
    {
        $bookings = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $booking) {
            $bookings[] = new ResourceItemResult($booking);
        }

        return $bookings;
    }
}
