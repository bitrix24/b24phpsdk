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

namespace Bitrix24\SDK\Services\Booking\WaitlistExternalData\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Booking\WaitlistExternalData\Result\WaitlistExternalDataResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['booking']))]
class WaitlistExternalData extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Retrieves external data links for a waitlist entry.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/external-data/booking-v1-waitlist-externaldata-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.waitlist.externalData.list',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/external-data/booking-v1-waitlist-externaldata-list.html',
        'Retrieves external data links for a waitlist entry.'
    )]
    public function list(int $waitListId): WaitlistExternalDataResult
    {
        return new WaitlistExternalDataResult(
            $this->core->call('booking.v1.waitlist.externalData.list', [
                'waitListId' => $waitListId,
            ])
        );
    }

    /**
     * Sets external data links for a waitlist entry.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/external-data/booking-v1-waitlist-externaldata-set.html
     *
     * @param array<int, array<string, string>> $externalData
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.waitlist.externalData.set',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/external-data/booking-v1-waitlist-externaldata-set.html',
        'Sets external data links for a waitlist entry.'
    )]
    public function set(int $waitListId, array $externalData): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('booking.v1.waitlist.externalData.set', [
                'waitListId' => $waitListId,
                'externalData' => $externalData,
            ])
        );
    }

    /**
     * Removes external data links from a waitlist entry.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/external-data/booking-v1-waitlist-externaldata-unset.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.waitlist.externalData.unset',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/external-data/booking-v1-waitlist-externaldata-unset.html',
        'Removes external data links from a waitlist entry.'
    )]
    public function unset(int $waitListId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('booking.v1.waitlist.externalData.unset', [
                'waitListId' => $waitListId,
            ])
        );
    }
}
