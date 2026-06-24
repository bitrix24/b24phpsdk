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

namespace Bitrix24\SDK\Services\Booking\Resource\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Booking\Resource\Result\AddedResourceResult;
use Bitrix24\SDK\Services\Booking\Resource\Result\ResourceResult;
use Bitrix24\SDK\Services\Booking\Resource\Result\ResourcesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['booking']))]
class Resource extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new resource.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-add.html
     *
     * @param array<string, mixed> $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.resource.add',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-add.html',
        'Adds a new resource.'
    )]
    public function add(array $fields): AddedResourceResult
    {
        return new AddedResourceResult(
            $this->core->call('booking.v1.resource.add', [
                'fields' => $fields,
            ])
        );
    }

    /**
     * Updates a resource.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-update.html
     *
     * @param array<string, mixed> $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.resource.update',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-update.html',
        'Updates a resource.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('booking.v1.resource.update', [
                'id' => $id,
                'fields' => $fields,
            ])
        );
    }

    /**
     * Retrieves a resource.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.resource.get',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-get.html',
        'Retrieves a resource.'
    )]
    public function get(int $id): ResourceResult
    {
        return new ResourceResult(
            $this->core->call('booking.v1.resource.get', [
                'id' => $id,
            ])
        );
    }

    /**
     * Retrieves a list of resources.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-list.html
     *
     * @param array<string, mixed> $filter
     * @param array<string, string> $order
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.resource.list',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-list.html',
        'Retrieves a list of resources.'
    )]
    public function list(array $filter = [], array $order = []): ResourcesResult
    {
        return new ResourcesResult(
            $this->core->call('booking.v1.resource.list', [
                'filter' => $filter,
                'order' => $order,
            ])
        );
    }

    /**
     * Deletes a resource.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.resource.delete',
        'https://apidocs.bitrix24.com/api-reference/booking/resource/booking-v1-resource-delete.html',
        'Deletes a resource.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('booking.v1.resource.delete', [
                'id' => $id,
            ])
        );
    }
}
