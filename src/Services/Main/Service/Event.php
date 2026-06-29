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
use Bitrix24\SDK\Core\Exceptions\UnknownScopeCodeException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Main\Result\EventHandlerBindResult;
use Bitrix24\SDK\Services\Main\Result\EventHandlersResult;
use Bitrix24\SDK\Services\Main\Result\EventHandlerUnbindResult;
use Bitrix24\SDK\Services\Main\Result\EventListResult;

#[ApiServiceMetadata(new Scope([]))]
class Event extends AbstractService
{
    /**
     * Displays events from the general list of events.
     *
     *
     * @throws BaseException
     * @throws TransportException
     * @throws UnknownScopeCodeException
     * @link https://training.bitrix24.com/rest_help/general/events_method/events.php
     */
    #[ApiEndpointMetadata(
        'events',
        'https://training.bitrix24.com/rest_help/general/events_method/events.php',
        'Displays events from the general list of events.'
    )]
    public function list(?string $scopeCode = null): EventListResult
    {
        return new EventListResult(
            $this->core->call(
                'events',
                $scopeCode !== null ? ['scope' => (new Scope([$scopeCode]))->getScopeCodes()[0]] : []
            )
        );
    }

    /**
     * Installs a new event handler.
     *
     * @param EventType $eventType online or offline delivery type (offline events are queued and read via event.offline.*)
     * @param string|null $authConnector offline-events source key used to avoid event cycles
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/events/event-bind.html
     */
    #[ApiEndpointMetadata(
        'event.bind',
        'https://apidocs.bitrix24.com/api-reference/events/event-bind.html',
        'Installs a new event handler.'
    )]
    public function bind(
        string $eventCode,
        string $handlerUrl,
        ?int $userId = null,
        ?array $options = null,
        EventType $eventType = EventType::online,
        ?string $authConnector = null
    ): EventHandlerBindResult {
        $params = [
            'event' => $eventCode,
            'handler' => $handlerUrl,
            'event_type' => $eventType->value,
        ];
        if ($userId !== null) {
            $params['auth_type'] = $userId;
        }

        if ($authConnector !== null) {
            $params['auth_connector'] = $authConnector;
        }

        if (is_array($options)) {
            $params = array_merge($params, $options);
        }

        return new EventHandlerBindResult($this->core->call('event.bind', $params));
    }

    /**
     * Uninstalls a previously installed event handler.
     *
     * @param EventType $eventType online or offline delivery type, must match the type used at bind time
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/events/event-unbind.html
     */
    #[ApiEndpointMetadata(
        'event.unbind',
        'https://apidocs.bitrix24.com/api-reference/events/event-unbind.html',
        'Uninstalls a previously installed event handler.'
    )]
    public function unbind(
        string $eventCode,
        string $handlerUrl,
        ?int $userId = null,
        EventType $eventType = EventType::online
    ): EventHandlerUnbindResult {
        $params = [
            'event' => $eventCode,
            'handler' => $handlerUrl,
            'event_type' => $eventType->value,
        ];
        if ($userId !== null) {
            $params['auth_type'] = $userId;
        }

        return new EventHandlerUnbindResult($this->core->call('event.unbind', $params));
    }

    /**
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://training.bitrix24.com/rest_help/rest_sum/test_handler.php
     */
    #[ApiEndpointMetadata(
        'event.test',
        'https://training.bitrix24.com/rest_help/rest_sum/test_handler.php',
        'Test events'
    )]
    public function test(array $payload = []): Response
    {
        return $this->core->call('event.test', $payload);
    }

    /**
     * Obtaining a list of registered event handlers.
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://training.bitrix24.com/rest_help/general/events_method/event_get.php
     */
    #[ApiEndpointMetadata(
        'event.get',
        'https://training.bitrix24.com/rest_help/general/events_method/event_get.php',
        'Obtaining a list of registered event handlers.'
    )]
    public function get(): EventHandlersResult
    {
        return new EventHandlersResult($this->core->call('event.get'));
    }
}