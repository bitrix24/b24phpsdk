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

namespace Bitrix24\SDK\Services\Biconnector\Connector\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Biconnector\Connector\Result\AddedConnectorBatchResult;
use Bitrix24\SDK\Services\Biconnector\Connector\Result\ConnectorItemResult;
use Bitrix24\SDK\Services\Biconnector\Connector\Result\DeletedConnectorBatchResult;
use Bitrix24\SDK\Services\Biconnector\Connector\Result\UpdatedConnectorBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['biconnector']))]
class Batch
{
    /**
     * Batch constructor
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list connectors
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-list.html
     *
     * @return Generator<int, ConnectorItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'biconnector.connector.list',
        'https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-list.html',
        'Batch list connectors'
    )]
    public function list(
        array $order = [],
        array $filter = [],
        array $select = [],
        ?int $limit = null
    ): Generator {
        $this->log->debug(
            'batchList',
            [
                'order'  => $order,
                'filter' => $filter,
                'select' => $select,
                'limit'  => $limit,
            ]
        );

        $connectorListGenerator = $this->batch->getTraversableListWithCount(
            'biconnector.connector.list',
            $order,
            $filter,
            $select,
            $limit
        );
        foreach ($connectorListGenerator as $key => $value) {
            yield $key => new ConnectorItemResult($value);
        }
    }

    /**
     * Batch add connectors
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-add.html
     *
     * @param array<int, array{
     *     name: string,
     *     code: string,
     *     description?: string,
     *     pictureUrl?: string,
     *     settings?: array,
     *     isEnabled?: bool
     * }> $connectors
     *
     * @return Generator<int, AddedConnectorBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'biconnector.connector.add',
        'https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-add.html',
        'Batch add connectors'
    )]
    public function add(array $connectors): Generator
    {
        $items = [];
        foreach ($connectors as $item) {
            $items[] = [
                'fields' => $item,
            ];
        }

        foreach ($this->batch->addEntityItems('biconnector.connector.add', $items) as $key => $item) {
            yield $key => new AddedConnectorBatchResult($item);
        }
    }

    /**
     * Batch update connectors
     *
     * Update elements in array with structure:
     * id => [  // Connector id
     *     'fields' => [] // Connector fields to update
     * ]
     *
     * @param array<int, array> $entityItems
     *
     * @return Generator<int, UpdatedConnectorBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'biconnector.connector.update',
        'https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-update.html',
        'Batch update connectors'
    )]
    public function update(array $entityItems): Generator
    {
        foreach (
            $this->batch->updateEntityItems(
                'biconnector.connector.update',
                $entityItems
            ) as $key => $item
        ) {
            yield $key => new UpdatedConnectorBatchResult($item);
        }
    }

    /**
     * Batch delete connectors
     *
     * @param int[] $connectorIds
     *
     * @return Generator<int, DeletedConnectorBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'biconnector.connector.delete',
        'https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-delete.html',
        'Batch delete connectors'
    )]
    public function delete(array $connectorIds): Generator
    {
        foreach (
            $this->batch->deleteEntityItems(
                'biconnector.connector.delete',
                $connectorIds
            ) as $key => $item
        ) {
            yield $key => new DeletedConnectorBatchResult($item);
        }
    }
}
