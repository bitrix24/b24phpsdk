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

namespace Bitrix24\SDK\Services\Booking\ResourceType\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Booking\ResourceType\Result\AddedResourceTypeResult;
use Bitrix24\SDK\Services\Booking\ResourceType\Result\ResourceTypeResult;
use Bitrix24\SDK\Services\Booking\ResourceType\Result\ResourceTypesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['booking']))]
class ResourceType extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new resource type.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-add.html
     *
     * @param array<string, mixed> $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.resourceType.add',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-add.html',
        'Adds a new resource type.'
    )]
    public function add(array $fields): AddedResourceTypeResult
    {
        return new AddedResourceTypeResult(
            $this->core->call('booking.v1.resourceType.add', [
                'fields' => $fields,
            ])
        );
    }

    /**
     * Updates a resource type.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-update.html
     *
     * @param array<string, mixed> $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.resourceType.update',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-update.html',
        'Updates a resource type.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('booking.v1.resourceType.update', [
                'id' => $id,
                'fields' => $fields,
            ])
        );
    }

    /**
     * Retrieves a resource type.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.resourceType.get',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-get.html',
        'Retrieves a resource type.'
    )]
    public function get(int $id): ResourceTypeResult
    {
        return new ResourceTypeResult(
            $this->core->call('booking.v1.resourceType.get', [
                'id' => $id,
            ])
        );
    }

    /**
     * Retrieves a list of resource types.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-list.html
     *
     * @param array<string, mixed> $filter
     * @param array<string, string> $order
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.resourceType.list',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-list.html',
        'Retrieves a list of resource types.'
    )]
    public function list(array $filter = [], array $order = []): ResourceTypesResult
    {
        return new ResourceTypesResult(
            $this->core->call('booking.v1.resourceType.list', [
                'filter' => $filter,
                'order' => $order,
            ])
        );
    }

    /**
     * Deletes a resource type.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.resourceType.delete',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/resource-type/booking-v1-resourcetype-delete.html',
        'Deletes a resource type.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('booking.v1.resourceType.delete', [
                'id' => $id,
            ])
        );
    }
}
