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
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\User\Result\UserResult;
use Bitrix24\SDK\Services\IM\User\Result\UsersResult;

#[ApiServiceMetadata(new Scope(['im']))]
class User extends AbstractService
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.user.get',
        'https://apidocs.bitrix24.com/api-reference/chats/users/im-user-get.html',
        'Get data for the current user or by user ID'
    )]
    public function get(?int $userId = null): UserResult
    {
        $payload = [];
        if ($userId !== null) {
            $payload['ID'] = $userId;
        }

        return new UserResult($this->core->call('im.user.get', $payload));
    }

    /**
     * @param int[] $userIds
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.user.list.get',
        'https://apidocs.bitrix24.com/api-reference/chats/users/im-user-list-get.html',
        'Get data for a list of users by their IDs'
    )]
    public function listGet(array $userIds): UsersResult
    {
        return new UsersResult($this->core->call('im.user.list.get', [
            'ID' => $userIds,
        ]));
    }
}
