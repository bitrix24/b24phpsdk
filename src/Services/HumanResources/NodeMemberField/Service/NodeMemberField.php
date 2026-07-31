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

namespace Bitrix24\SDK\Services\HumanResources\NodeMemberField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\HumanResources\NodeMemberField\Result\NodeMemberFieldResult;
use Bitrix24\SDK\Services\HumanResources\NodeMemberField\Result\NodeMemberFieldsResult;

#[ApiServiceMetadata(new Scope(['humanresources']))]
class NodeMemberField extends AbstractService
{
    /**
     * @param string[] $select
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.member.field.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node-member/humanresources-node-member-field-get.html',
        'Get node member field metadata by name',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): NodeMemberFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new NodeMemberFieldResult(
            $this->core->call('humanresources.node.member.field.get', $params, ApiVersion::v3)
        );
    }

    /**
     * @param string[] $select
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.member.field.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node-member/humanresources-node-member-field-list.html',
        'Get node member field metadata list',
        ApiVersion::v3
    )]
    public function list(array $select = []): NodeMemberFieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];

        return new NodeMemberFieldsResult(
            $this->core->call('humanresources.node.member.field.list', $params, ApiVersion::v3)
        );
    }
}
