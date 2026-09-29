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

namespace Bitrix24\SDK\Services\IMBot\Event\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IMBot\Event\Result\EventsResult;

#[ApiServiceMetadata(new Scope(['imbot']))]
class Event extends AbstractService
{
    /**
     * Poll pending events for the bot (fetch mode).
     *
     * On the first call omit $offset. Pass the returned nextOffset in subsequent calls
     * to acknowledge already-processed events and move the queue cursor forward.
     *
     * @param int         $botId         Bot ID.
     * @param int|null    $offset        Acknowledges all events with ID < this value. Omit on the first call.
     * @param int|null    $limit         Maximum events to return (1–1000). Default: 100.
     * @param bool        $withUserEvents Include user events (ONIMV2*) together with bot events.
     *                                   Requires `im` scope and a prior im.v2.Event.subscribe call.
     * @param string|null $botToken      Bot auth token (required for webhook auth, not needed for OAuth).
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/events/event-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.Event.get',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/events/event-get.html',
        'Poll pending events for the bot'
    )]
    public function get(
        int $botId,
        ?int $offset = null,
        ?int $limit = null,
        bool $withUserEvents = false,
        ?string $botToken = null,
    ): EventsResult {
        $params = ['botId' => $botId];

        if ($offset !== null) {
            $params['offset'] = $offset;
        }

        if ($limit !== null) {
            $params['limit'] = $limit;
        }

        if ($withUserEvents) {
            $params['withUserEvents'] = true;
        }

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new EventsResult($this->core->call('imbot.v2.Event.get', $params));
    }
}
