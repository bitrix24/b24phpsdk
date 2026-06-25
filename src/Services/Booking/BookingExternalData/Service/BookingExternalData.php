<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Veronica Akhmetova <264936994+fatestr1ngs@users.noreply.github.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Booking\BookingExternalData\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Booking\BookingExternalData\Result\BookingExternalDataResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['booking']))]
class BookingExternalData extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Retrieves external data links for a booking.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/external-data/booking-v1-booking-externaldata-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.booking.externalData.list',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/external-data/booking-v1-booking-externaldata-list.html',
        'Retrieves external data links for a booking.'
    )]
    public function list(int $bookingId): BookingExternalDataResult
    {
        return new BookingExternalDataResult(
            $this->core->call('booking.v1.booking.externalData.list', [
                'bookingId' => $bookingId,
            ])
        );
    }

    /**
     * Sets external data links for a booking.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/external-data/booking-v1-booking-externaldata-set.html
     *
     * @param array<int, array<string, string>> $externalData
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.booking.externalData.set',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/external-data/booking-v1-booking-externaldata-set.html',
        'Sets external data links for a booking.'
    )]
    public function set(int $bookingId, array $externalData): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('booking.v1.booking.externalData.set', [
                'bookingId' => $bookingId,
                'externalData' => $externalData,
            ])
        );
    }

    /**
     * Removes external data links from a booking.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/external-data/booking-v1-booking-externaldata-unset.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.booking.externalData.unset',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/external-data/booking-v1-booking-externaldata-unset.html',
        'Removes external data links from a booking.'
    )]
    public function unset(int $bookingId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('booking.v1.booking.externalData.unset', [
                'bookingId' => $bookingId,
            ])
        );
    }
}
