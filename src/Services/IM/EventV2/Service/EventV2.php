<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\EventV2\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\EmptyResult;
use Bitrix24\SDK\Services\AbstractService;

/**
 * IM v2 event subscription service.
 *
 * @see https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/im.v2/events/
 */
#[ApiServiceMetadata(new Scope(['im']))]
class EventV2 extends AbstractService
{
    /**
     * Subscribe the current user to message event recording.
     *
     * After subscribing, events become available via im.v2.Event.get. Idempotent.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/im.v2/events/event-subscribe.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.v2.Event.subscribe',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/im.v2/events/event-subscribe.html',
        'Subscribe the current user to message event recording'
    )]
    public function subscribe(): EmptyResult
    {
        return new EmptyResult($this->core->call('im.v2.Event.subscribe'));
    }

    /**
     * Unsubscribe the current user from message event recording.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/im.v2/events/event-unsubscribe.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.v2.Event.unsubscribe',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/im.v2/events/event-unsubscribe.html',
        'Unsubscribe the current user from message event recording'
    )]
    public function unsubscribe(): EmptyResult
    {
        return new EmptyResult($this->core->call('im.v2.Event.unsubscribe'));
    }

    /**
     * Poll pending message events for the current user.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/im.v2/events/event-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.v2.Event.get',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/im.v2/events/event-get.html',
        'Poll pending message events for the current user'
    )]
    public function get(): EmptyResult
    {
        return new EmptyResult($this->core->call('im.v2.Event.get'));
    }
}
