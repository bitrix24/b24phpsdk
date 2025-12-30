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

namespace Bitrix24\SDK\Services\SonetGroup\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\SonetGroup\Result\SonetGroupGetItemResult;
use Bitrix24\SDK\Services\SonetGroup\Result\SonetGetGroupsResult;
use Bitrix24\SDK\Services\SonetGroup\Result\SonetGroupsResult;
use Bitrix24\SDK\Services\SonetGroup\Result\SonetGroupResult;
use Bitrix24\SDK\Services\SonetGroup\Result\SonetGroupUserOperationResult;
use Bitrix24\SDK\Services\SonetGroup\Result\UserGroupsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sonet_group', 'socialnetwork']))]
class SonetGroup extends AbstractService
{
    /**
     * SonetGroup constructor.
     */
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Creates a social network group/project.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-create.html
     *
     * @param array $fields Field values for creating a group
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sonet_group.create',
        'https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-create.html',
        'Creates a social network group/project.'
    )]
    public function create(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('sonet_group.create', $fields)
        );
    }

    /**
     * Modifies group parameters.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-update.html
     *
     * @param int   $groupId Group identifier
     * @param array $fields  Field values for update
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sonet_group.update',
        'https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-update.html',
        'Modifies group parameters.'
    )]
    public function update(int $groupId, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('sonet_group.update', array_merge(['GROUP_ID' => $groupId], $fields))
        );
    }

    /**
     * Deletes a social network group.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-delete.html
     *
     * @param int $groupId Group identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sonet_group.delete',
        'https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-delete.html',
        'Deletes a social network group.'
    )]
    public function delete(int $groupId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sonet_group.delete', [
                'GROUP_ID' => $groupId,
            ])
        );
    }

    /**
     * Gets detailed information about a specific workgroup.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sonet-group/socialnetwork-api-workgroup-get.html
     *
     * @param int   $groupId Group identifier
     * @param array $select  Additional fields to retrieve
     * @param string|null $mode Request mode
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'socialnetwork.api.workgroup.get',
        'https://apidocs.bitrix24.com/api-reference/sonet-group/socialnetwork-api-workgroup-get.html',
        'Gets detailed information about a specific workgroup.'
    )]
    public function get(int $groupId, array $select = [], ?string $mode = null): SonetGroupResult
    {
        $params = ['groupId' => $groupId];

        if ($select !== []) {
            $params['select'] = $select;
        }

        if ($mode !== null) {
            $params['mode'] = $mode;
        }

        return new SonetGroupResult(
            $this->core->call('socialnetwork.api.workgroup.get', [
                'params' => $params
            ])
        );
    }

    /**
     * Gets list of workgroups with filtering.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sonet-group/socialnetwork-api-workgroup-list.html
     *
     * @param array $filter   Filter conditions
     * @param array $select   Array of fields to retrieve
     * @param bool  $isAdmin  Admin privilege bypass
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'socialnetwork.api.workgroup.list',
        'https://apidocs.bitrix24.com/api-reference/sonet-group/socialnetwork-api-workgroup-list.html',
        'Gets list of workgroups with filtering.'
    )]
    public function list(array $filter = [], array $select = [], bool $isAdmin = false): SonetGroupsResult
    {
        $params = [];

        if ($filter !== []) {
            $params['filter'] = $filter;
        }

        if ($select !== []) {
            $params['select'] = $select;
        }

        if ($isAdmin) {
            $params['IS_ADMIN'] = 'Y';
        }

        return new SonetGroupsResult(
            $this->core->call('socialnetwork.api.workgroup.list', $params)
        );
    }

    /**
     * Gets list of social network groups (simpler version).
     *
     * @link https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-get.html
     *
     * @param array $order   Sorting parameters
     * @param array $filter  Filter conditions
     * @param bool  $isAdmin Admin privilege bypass
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sonet_group.get',
        'https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-get.html',
        'Gets list of social network groups (simpler version).'
    )]
    public function getGroups(array $order = [], array $filter = [], bool $isAdmin = false): SonetGetGroupsResult
    {
        $params = [];

        if ($order !== []) {
            $params['ORDER'] = $order;
        }

        if ($filter !== []) {
            $params['FILTER'] = $filter;
        }

        if ($isAdmin) {
            $params['IS_ADMIN'] = 'Y';
        }

        return new SonetGetGroupsResult(
            $this->core->call('sonet_group.get', $params)
        );
    }

    /**
     * Gets list of current user's groups.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-user-groups.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sonet_group.user.groups',
        'https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-user-groups.html',
        "Gets list of current user's groups."
    )]
    public function getUserGroups(): UserGroupsResult
    {
        return new UserGroupsResult(
            $this->core->call('sonet_group.user.groups', [])
        );
    }

    /**
     * Adds users to group without invitation process.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sonet-group/members/sonet-group-user-add.html
     *
     * @param int       $groupId Group ID
     * @param int|array $userId  User ID or array of user IDs
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sonet_group.user.add',
        'https://apidocs.bitrix24.com/api-reference/sonet-group/members/sonet-group-user-add.html',
        'Adds users to group without invitation process.'
    )]
    public function addUser(int $groupId, int|array $userId): SonetGroupUserOperationResult
    {
        return new SonetGroupUserOperationResult(
            $this->core->call('sonet_group.user.add', [
                'GROUP_ID' => $groupId,
                'USER_ID' => $userId,
            ])
        );
    }

    /**
     * Removes users from group.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sonet-group/members/sonet-group-user-delete.html
     *
     * @param int       $groupId Group ID
     * @param int|array $userId  User ID or array of user IDs
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sonet_group.user.delete',
        'https://apidocs.bitrix24.com/api-reference/sonet-group/members/sonet-group-user-delete.html',
        'Removes users from group.'
    )]
    public function deleteUser(int $groupId, int|array $userId): SonetGroupUserOperationResult
    {
        return new SonetGroupUserOperationResult(
            $this->core->call('sonet_group.user.delete', [
                'GROUP_ID' => $groupId,
                'USER_ID' => $userId,
            ])
        );
    }

    /**
     * Changes group owner.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-setowner.html
     *
     * @param int $groupId Group ID
     * @param int $userId  New owner user ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sonet_group.setowner',
        'https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-setowner.html',
        'Changes group owner.'
    )]
    public function setOwner(int $groupId, int $userId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('sonet_group.setowner', [
                'GROUP_ID' => $groupId,
                'USER_ID' => $userId,
            ])
        );
    }
}
