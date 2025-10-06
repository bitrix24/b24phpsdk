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

namespace Bitrix24\SDK\Services\Calendar\Resource\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Calendar\Resource\Result\BookingsResult;
use Bitrix24\SDK\Services\Calendar\Resource\Result\ResourcesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['calendar']))]
class Resource extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new resource.
     *
     * @link https://apidocs.bitrix24.com/api-reference/calendar/resource/calendar-resource-add.html
     *
     * @param string $name Resource name
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.resource.add',
        'https://apidocs.bitrix24.com/api-reference/calendar/resource/calendar-resource-add.html',
        'Method adds a new resource.'
    )]
    public function add(string $name): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('calendar.resource.add', [
                'name' => $name,
            ])
        );
    }

    /**
     * Updates a resource.
     *
     * @link https://apidocs.bitrix24.com/api-reference/calendar/resource/calendar-resource-update.html
     *
     * @param int $resourceId Resource identifier
     * @param string $name New name of the resource
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.resource.update',
        'https://apidocs.bitrix24.com/api-reference/calendar/resource/calendar-resource-update.html',
        'Method updates a resource.'
    )]
    public function update(int $resourceId, string $name): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('calendar.resource.update', [
                'resourceId' => $resourceId,
                'name' => $name,
            ])
        );
    }

    /**
     * Retrieves a list of all resources.
     *
     * @link https://apidocs.bitrix24.com/api-reference/calendar/resource/calendar-resource-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.resource.list',
        'https://apidocs.bitrix24.com/api-reference/calendar/resource/calendar-resource-list.html',
        'Method retrieves a list of all resources.'
    )]
    public function list(): ResourcesResult
    {
        return new ResourcesResult(
            $this->core->call('calendar.resource.list')
        );
    }

    /**
     * Retrieves resource bookings based on a filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/calendar/resource/calendar-resource-booking-list.html
     *
     * @param array $filter Filter fields. Must contain either resourceTypeIdList or resourceIdList
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.resource.booking.list',
        'https://apidocs.bitrix24.com/api-reference/calendar/resource/calendar-resource-booking-list.html',
        'Method retrieves resource bookings based on a filter.'
    )]
    public function bookingList(array $filter): BookingsResult
    {
        return new BookingsResult(
            $this->core->call('calendar.resource.booking.list', [
                'filter' => $filter,
            ])
        );
    }

    /**
     * Deletes a resource.
     *
     * @link https://apidocs.bitrix24.com/api-reference/calendar/resource/calendar-resource-delete.html
     *
     * @param int $resourceId Resource identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.resource.delete',
        'https://apidocs.bitrix24.com/api-reference/calendar/resource/calendar-resource-delete.html',
        'Method deletes a resource.'
    )]
    public function delete(int $resourceId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('calendar.resource.delete', [
                'resourceId' => $resourceId,
            ])
        );
    }
}
