<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Main\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Main\Result\OfflineEventClearResult;
use Bitrix24\SDK\Services\Main\Result\OfflineEventErrorResult;
use Bitrix24\SDK\Services\Main\Result\OfflineEventPacketResult;
use Bitrix24\SDK\Services\Main\Result\OfflineEventsResult;

#[ApiServiceMetadata(new Scope([]))]
class OfflineEvent extends AbstractService
{
    /**
     * Returns the first offline events in the queue according to the filter.
     *
     * @param array<string, mixed> $filter records filter (ID, TIMESTAMP_X, EVENT_NAME, MESSAGE_ID)
     * @param string|null $authConnector offline-events source key, selects the queue to read
     * @param bool $clear when false, reserves the packet and returns a process_id instead of clearing it
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/events/event-offline-get.html
     */
    #[ApiEndpointMetadata(
        'event.offline.get',
        'https://apidocs.bitrix24.com/api-reference/events/event-offline-get.html',
        'Returns the first offline events in the queue according to the filter.'
    )]
    public function get(array $filter = [], ?string $authConnector = null, bool $clear = true): OfflineEventPacketResult
    {
        $params = ['clear' => $clear ? 1 : 0];
        if ($filter !== []) {
            $params['filter'] = $filter;
        }

        if ($authConnector !== null) {
            $params['auth_connector'] = $authConnector;
        }

        return new OfflineEventPacketResult($this->core->call('event.offline.get', $params));
    }

    /**
     * Reads the current offline-events queue without changing its state.
     *
     * @param array<string, mixed> $filter records filter (ID, TIMESTAMP_X, EVENT_NAME, MESSAGE_ID, PROCESS_ID, ERROR)
     * @param string|null $authConnector offline-events source key, selects the queue to read; without it
     *                                    only source-less events are returned
     * @param array<string, string> $order records order
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/events/event-offline-list.html
     */
    #[ApiEndpointMetadata(
        'event.offline.list',
        'https://apidocs.bitrix24.com/api-reference/events/event-offline-list.html',
        'Reads the current offline-events queue without changing its state.'
    )]
    public function list(array $filter = [], ?string $authConnector = null, array $order = []): OfflineEventsResult
    {
        $params = [];
        if ($filter !== []) {
            $params['filter'] = $filter;
        }

        if ($authConnector !== null) {
            $params['auth_connector'] = $authConnector;
        }

        if ($order !== []) {
            $params['order'] = $order;
        }

        return new OfflineEventsResult($this->core->call('event.offline.list', $params));
    }

    /**
     * Clears records in the offline-events queue.
     *
     * @param string $processId reserved packet identifier returned by get() with clear=false
     * @param int[] $id record ids to clear; by default clears all records of the process_id
     * @param string[] $messageId MESSAGE_ID values to clear; ignored when $id is provided
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/events/event-offline-clear.html
     */
    #[ApiEndpointMetadata(
        'event.offline.clear',
        'https://apidocs.bitrix24.com/api-reference/events/event-offline-clear.html',
        'Clears records in the offline-events queue.'
    )]
    public function clear(string $processId, array $id = [], array $messageId = []): OfflineEventClearResult
    {
        $params = ['process_id' => $processId];
        if ($id !== []) {
            $params['id'] = $id;
        }

        if ($messageId !== []) {
            $params['message_id'] = $messageId;
        }

        return new OfflineEventClearResult($this->core->call('event.offline.clear', $params));
    }

    /**
     * Marks offline-events records as processed with an error.
     *
     * @param string $processId process identifier handling the records
     * @param string[] $messageId MESSAGE_ID values to mark as errored
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/events/event-offline-error.html
     */
    #[ApiEndpointMetadata(
        'event.offline.error',
        'https://apidocs.bitrix24.com/api-reference/events/event-offline-error.html',
        'Marks offline-events records as processed with an error.'
    )]
    public function error(string $processId, array $messageId = []): OfflineEventErrorResult
    {
        $params = ['process_id' => $processId];
        if ($messageId !== []) {
            $params['message_id'] = $messageId;
        }

        return new OfflineEventErrorResult($this->core->call('event.offline.error', $params));
    }
}
