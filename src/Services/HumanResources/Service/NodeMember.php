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

namespace Bitrix24\SDK\Services\HumanResources\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\HumanResources\Result\NodeMemberOperationResult;
use Bitrix24\SDK\Services\HumanResources\Result\NodeMemberRemoveResult;

#[ApiServiceMetadata(new Scope(['humanresources']))]
class NodeMember extends AbstractService
{
    /**
     * @param int[] $userIds
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.member.add',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node-member/humanresources-node-member-add.html',
        'Add node members',
        ApiVersion::v3
    )]
    public function add(int $nodeId, array $userIds, string $role): NodeMemberOperationResult
    {
        $this->guardPositiveId($nodeId);
        $this->guardNonEmptyString($role, 'node member role must not be empty');

        return new NodeMemberOperationResult(
            $this->core->call(
                'humanresources.node.member.add',
                ['nodeId' => $nodeId, 'userIds' => $userIds, 'role' => $role],
                ApiVersion::v3
            )
        );
    }

    /**
     * @param int[] $userIds
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.member.move',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node-member/humanresources-node-member-move.html',
        'Move node members',
        ApiVersion::v3
    )]
    public function move(int $nodeId, array $userIds, ?string $role = null): NodeMemberOperationResult
    {
        $this->guardPositiveId($nodeId);

        $params = ['nodeId' => $nodeId, 'userIds' => $userIds];
        if ($role !== null) {
            $this->guardNonEmptyString($role, 'node member role must not be empty');
            $params['role'] = $role;
        }

        return new NodeMemberOperationResult(
            $this->core->call('humanresources.node.member.move', $params, ApiVersion::v3)
        );
    }

    /**
     * @param int[] $userIds
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.member.remove',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node-member/humanresources-node-member-remove.html',
        'Remove node members',
        ApiVersion::v3
    )]
    public function remove(int $nodeId, array $userIds): NodeMemberRemoveResult
    {
        $this->guardPositiveId($nodeId);

        return new NodeMemberRemoveResult(
            $this->core->call(
                'humanresources.node.member.remove',
                ['nodeId' => $nodeId, 'userIds' => $userIds],
                ApiVersion::v3
            )
        );
    }

    /**
     * @param array<string, int[]> $userIds
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.member.set',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node-member/humanresources-node-member-set.html',
        'Set node members',
        ApiVersion::v3
    )]
    public function set(int $nodeId, array $userIds): NodeMemberOperationResult
    {
        $this->guardPositiveId($nodeId);

        return new NodeMemberOperationResult(
            $this->core->call(
                'humanresources.node.member.set',
                ['nodeId' => $nodeId, 'userIds' => $userIds],
                ApiVersion::v3
            )
        );
    }
}
