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

namespace Bitrix24\SDK\Services\Booking\ResourceSlots\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Booking\ResourceSlots\Result\ResourceSlotsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['booking']))]
class ResourceSlots extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Sets slots for a resource.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/slots/booking-v1-resource-slots-set.html
     *
     * @param array<int, array<string, mixed>> $slots
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.resource.slots.set',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/slots/booking-v1-resource-slots-set.html',
        'Sets slots for a resource.'
    )]
    public function set(int $resourceId, array $slots): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('booking.v1.resource.slots.set', [
                'resourceId' => $resourceId,
                'slots' => $slots,
            ])
        );
    }

    /**
     * Retrieves slot settings for a resource.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/slots/booking-v1-resource-slots-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.resource.slots.list',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/slots/booking-v1-resource-slots-list.html',
        'Retrieves slot settings for a resource.'
    )]
    public function list(int $resourceId): ResourceSlotsResult
    {
        return new ResourceSlotsResult(
            $this->core->call('booking.v1.resource.slots.list', [
                'resourceId' => $resourceId,
            ])
        );
    }

    /**
     * Removes slots for a resource.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/slots/booking-v1-resource-slots-unset.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.resource.slots.unset',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/slots/booking-v1-resource-slots-unset.html',
        'Removes slots for a resource.'
    )]
    public function unset(int $resourceId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('booking.v1.resource.slots.unset', [
                'resourceId' => $resourceId,
            ])
        );
    }
}
