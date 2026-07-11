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
use Bitrix24\SDK\Services\HumanResources\Result\NodeCommunicationEditResult;
use Bitrix24\SDK\Services\HumanResources\Result\NodeCommunicationResult;

#[ApiServiceMetadata(new Scope(['humanresources']))]
class NodeCommunication extends AbstractService
{
    /**
     * @param array<string, mixed> $options
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.communication.edit',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node-communication/humanresources-node-communication-edit.html',
        'Edit node communications',
        ApiVersion::v3
    )]
    public function edit(int $nodeId, string $communicationType, array $options = []): NodeCommunicationEditResult
    {
        $this->guardPositiveId($nodeId);
        $this->guardNonEmptyString($communicationType, 'communication type must not be empty');

        return new NodeCommunicationEditResult(
            $this->core->call(
                'humanresources.node.communication.edit',
                array_merge(['nodeId' => $nodeId, 'communicationType' => $communicationType], $options),
                ApiVersion::v3
            )
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'humanresources.node.communication.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/humanresources/node-communication/humanresources-node-communication-list.html',
        'Get node communications',
        ApiVersion::v3
    )]
    public function list(int $id): NodeCommunicationResult
    {
        $this->guardPositiveId($id);

        return new NodeCommunicationResult(
            $this->core->call('humanresources.node.communication.list', ['id' => $id], ApiVersion::v3)
        );
    }
}
