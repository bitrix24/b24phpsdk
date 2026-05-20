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

namespace Bitrix24\SDK\Services\Booking\WaitlistClient\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Booking\WaitlistClient\Result\WaitlistClientsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['booking']))]
class WaitlistClient extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Returns clients linked to a waitlist entry.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/client/booking-v1-waitlist-client-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.waitlist.client.list',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/client/booking-v1-waitlist-client-list.html',
        'Returns clients linked to a waitlist entry.'
    )]
    public function list(int $waitListId): WaitlistClientsResult
    {
        return new WaitlistClientsResult(
            $this->core->call('booking.v1.waitlist.client.list', [
                'waitListId' => $waitListId,
            ])
        );
    }

    /**
     * Sets clients for a waitlist entry.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/client/booking-v1-waitlist-client-set.html
     *
     * @param array<int, array<string, mixed>> $clients
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.waitlist.client.set',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/client/booking-v1-waitlist-client-set.html',
        'Sets clients for a waitlist entry.'
    )]
    public function set(int $waitListId, array $clients): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('booking.v1.waitlist.client.set', [
                'waitListId' => $waitListId,
                'clients' => $clients,
            ])
        );
    }

    /**
     * Removes clients from a waitlist entry.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/client/booking-v1-waitlist-client-unset.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.waitlist.client.unset',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/client/booking-v1-waitlist-client-unset.html',
        'Removes clients from a waitlist entry.'
    )]
    public function unset(int $waitListId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('booking.v1.waitlist.client.unset', [
                'waitListId' => $waitListId,
            ])
        );
    }
}
