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

namespace Bitrix24\SDK\Services\HumanResources\NodeField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\HumanResources\NodeField\Result\NodeFieldResult;
use Bitrix24\SDK\Services\HumanResources\NodeField\Result\NodeFieldsResult;

#[ApiServiceMetadata(new Scope(['humanresources']))]
class NodeField extends AbstractService
{
    /**
     * @param string[] $select
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.field.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node/humanresources-node-field-get.html',
        'Get org-structure node field metadata by name',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): NodeFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new NodeFieldResult(
            $this->core->call('humanresources.node.field.get', $params, ApiVersion::v3)
        );
    }

    /**
     * @param string[] $select
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.field.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node/humanresources-node-field-list.html',
        'Get org-structure node field metadata list',
        ApiVersion::v3
    )]
    public function list(array $select = []): NodeFieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];

        return new NodeFieldsResult(
            $this->core->call('humanresources.node.field.list', $params, ApiVersion::v3)
        );
    }
}
