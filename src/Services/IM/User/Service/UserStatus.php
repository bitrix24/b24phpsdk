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

namespace Bitrix24\SDK\Services\IM\User\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\User\Result\UserStatusResult;
use Bitrix24\SDK\Services\IM\User\UserStatusType;

#[ApiServiceMetadata(new Scope(['im']))]
class UserStatus extends AbstractService
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.user.status.get',
        'https://apidocs.bitrix24.com/api-reference/chats/users/im-user-status-get.html',
        'Get current user status'
    )]
    public function get(): UserStatusResult
    {
        return new UserStatusResult($this->core->call('im.user.status.get'));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.user.status.set',
        'https://apidocs.bitrix24.com/api-reference/chats/users/im-user-status-set.html',
        'Set current user status'
    )]
    public function set(UserStatusType $userStatusType): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.user.status.set', [
            'STATUS' => $userStatusType->value,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.user.status.idle.start',
        'https://apidocs.bitrix24.com/api-reference/chats/users/im-user-status-idle-start.html',
        'Enable automatic Away status'
    )]
    public function idleStart(): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.user.status.idle.start'));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.user.status.idle.end',
        'https://apidocs.bitrix24.com/api-reference/chats/users/im-user-status-idle-end.html',
        'Disable automatic Away status'
    )]
    public function idleEnd(): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.user.status.idle.end'));
    }
}
