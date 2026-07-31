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
use Bitrix24\SDK\Services\HumanResources\Result\NodeCountResult;
use Bitrix24\SDK\Services\HumanResources\Result\NodeResult;
use Bitrix24\SDK\Services\HumanResources\Result\NodesResult;

#[ApiServiceMetadata(new Scope(['humanresources']))]
class Node extends AbstractService
{
    /**
     * @param array<string, mixed> $optional
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.add',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node/humanresources-node-add.html',
        'Add an org-structure node',
        ApiVersion::v3
    )]
    public function add(string $type, string $name, int $parentId, array $optional = []): NodeResult
    {
        $this->guardNonEmptyString($type, 'node type must not be empty');
        $this->guardNonEmptyString($name, 'node name must not be empty');
        $this->guardPositiveId($parentId);

        return new NodeResult(
            $this->core->call(
                'humanresources.node.add',
                array_merge(['type' => $type, 'name' => $name, 'parentId' => $parentId], $optional),
                ApiVersion::v3
            )
        );
    }

    /**
     * @param string[] $select
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.children',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node/humanresources-node-children.html',
        'Get child org-structure nodes',
        ApiVersion::v3
    )]
    public function children(int $id, array $select = []): NodesResult
    {
        $this->guardPositiveId($id);

        $params = ['id' => $id];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new NodesResult(
            $this->core->call('humanresources.node.children', $params, ApiVersion::v3)
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.count',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node/humanresources-node-count.html',
        'Get org-structure node counters',
        ApiVersion::v3
    )]
    public function count(): NodeCountResult
    {
        return new NodeCountResult(
            $this->core->call('humanresources.node.count', [], ApiVersion::v3)
        );
    }

    /**
     * @param array<string, mixed> $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.edit',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node/humanresources-node-edit.html',
        'Edit an org-structure node',
        ApiVersion::v3
    )]
    public function edit(int $id, array $fields): NodeResult
    {
        $this->guardPositiveId($id);

        return new NodeResult(
            $this->core->call('humanresources.node.edit', array_merge(['id' => $id], $fields), ApiVersion::v3)
        );
    }

    /**
     * @param string[] $select
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node/humanresources-node-get.html',
        'Get an org-structure node by id',
        ApiVersion::v3
    )]
    public function get(int $id, array $select = []): NodeResult
    {
        $this->guardPositiveId($id);

        $params = ['id' => $id];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new NodeResult(
            $this->core->call('humanresources.node.get', $params, ApiVersion::v3)
        );
    }

    /**
     * @param string[]             $select
     * @param array<string, mixed> $pagination
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node/humanresources-node-list.html',
        'Get org-structure nodes',
        ApiVersion::v3
    )]
    public function list(string $type, array $select = [], array $pagination = []): NodesResult
    {
        $this->guardNonEmptyString($type, 'node type must not be empty');

        $params = ['type' => $type];
        if ($select !== []) {
            $params['select'] = $select;
        }

        if ($pagination !== []) {
            $params['pagination'] = $pagination;
        }

        return new NodesResult(
            $this->core->call('humanresources.node.list', $params, ApiVersion::v3)
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.move',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node/humanresources-node-move.html',
        'Move an org-structure node',
        ApiVersion::v3
    )]
    public function move(int $id, int $parentId): NodeResult
    {
        $this->guardPositiveId($id);
        $this->guardPositiveId($parentId);

        return new NodeResult(
            $this->core->call('humanresources.node.move', ['id' => $id, 'parentId' => $parentId], ApiVersion::v3)
        );
    }

    /**
     * @param array<string, mixed> $pagination
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.search',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node/humanresources-node-search.html',
        'Search org-structure nodes',
        ApiVersion::v3
    )]
    public function search(string $type, string $name, ?int $parentId = null, array $pagination = []): NodesResult
    {
        $this->guardNonEmptyString($type, 'node type must not be empty');
        $this->guardNonEmptyString($name, 'node name must not be empty');

        $params = ['type' => $type, 'name' => $name];
        if ($parentId !== null) {
            $params['parentId'] = $parentId;
        }

        if ($pagination !== []) {
            $params['pagination'] = $pagination;
        }

        return new NodesResult(
            $this->core->call('humanresources.node.search', $params, ApiVersion::v3)
        );
    }
}
