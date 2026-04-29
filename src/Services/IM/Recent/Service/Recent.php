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

namespace Bitrix24\SDK\Services\IM\Recent\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\Recent\Result\RecentsResult;

#[ApiServiceMetadata(new Scope(['im']))]
class Recent extends AbstractService
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.recent.get',
        'https://apidocs.bitrix24.com/api-reference/chats/recent/im-recent-get.html',
        'Get a shortened list of recent chats'
    )]
    public function get(?int $lastId = null, ?int $limit = null): RecentsResult
    {
        $payload = [
            'LAST_ID' => $lastId,
            'LIMIT' => $limit,
        ];

        return new RecentsResult($this->core->call('im.recent.get', array_filter(
            $payload,
            static fn(mixed $value): bool => $value !== null
        )));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.recent.list',
        'https://apidocs.bitrix24.com/api-reference/chats/recent/im-recent-list.html',
        'Get the list of recent dialogs with pagination'
    )]
    public function list(?int $lastId = null, ?int $limit = null): RecentsResult
    {
        $payload = [
            'LAST_ID' => $lastId,
            'LIMIT' => $limit,
        ];

        return new RecentsResult($this->core->call('im.recent.list', array_filter(
            $payload,
            static fn(mixed $value): bool => $value !== null
        )));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.recent.pin',
        'https://apidocs.bitrix24.com/api-reference/chats/recent/im-recent-pin.html',
        'Pin or unpin a dialog at the top of the recent list'
    )]
    public function pin(string $dialogId, bool $pin = true): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.recent.pin', [
            'DIALOG_ID' => $dialogId,
            'PIN' => $pin ? 'Y' : 'N',
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.recent.unread',
        'https://apidocs.bitrix24.com/api-reference/chats/recent/im-recent-unread.html',
        'Set or remove the unread mark on a dialog'
    )]
    public function unread(string $dialogId, string $action): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.recent.unread', [
            'DIALOG_ID' => $dialogId,
            'ACTION' => $action,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.recent.hide',
        'https://apidocs.bitrix24.com/api-reference/chats/recent/im-recent-hide.html',
        'Remove a dialog from the recent list'
    )]
    public function hide(string $dialogId): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.recent.hide', [
            'DIALOG_ID' => $dialogId,
        ]));
    }
}
