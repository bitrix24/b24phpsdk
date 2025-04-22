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

namespace Bitrix24\SDK\Services\Entity\Item\Service;

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
use Bitrix24\SDK\Services\Entity\Item\Result\ItemsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['entity']))]
class Item extends AbstractService
{
    public function __construct(
        public Batch $batch,
        CoreInterface $core,
        LoggerInterface $logger
    ) {
        parent::__construct($core, $logger);
    }

    /**
     * Add Storage Element
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/items/entity-item-add.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.item.add',
        'https://apidocs.bitrix24.com/api-reference/entity/items/entity-item-add.html',
        'Add Storage Element'
    )]
    public function add(string $entity, string $name, array $fields): AddedItemResult
    {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');
        $this->guardNonEmptyString($name, 'entity name must be an non empty string');

        return new AddedItemResult(
            $this->core->call(
                'entity.item.add',
                array_merge(
                    [
                        'ENTITY' => $entity,
                        'NAME' => $name,
                    ],
                    $fields
                ),
            )
        );
    }

    /**
     * Get the list of storage items
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/items/entity-item-get.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.item.get',
        'https://apidocs.bitrix24.com/api-reference/entity/items/entity-item-get.html',
        'Get the list of storage items'
    )]
    public function get(string $entity, array $sort = [], array $filter = [], int $startItem = 0): ItemsResult
    {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');

        return new ItemsResult(
            $this->core->call(
                'entity.item.get',
                [
                    'ENTITY' => $entity,
                    'SORT' => $sort,
                    'FILTER' => $filter,
                    'START' => $startItem,
                ],
            )
        );
    }

    /**
     * Delete Storage Element
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/items/entity-item-delete.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.item.delete',
        'https://apidocs.bitrix24.com/api-reference/entity/items/entity-item-delete.html',
        'Delete Storage Element'
    )]
    public function delete(string $entity, int $itemId): DeletedItemResult
    {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');

        return new DeletedItemResult(
            $this->core->call(
                'entity.item.delete',
                [
                    'ENTITY' => $entity,
                    'ID' => $itemId,
                ],
            )
        );
    }

    /**
     * Update Storage Item
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/items/entity-item-update.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.item.update',
        'https://apidocs.bitrix24.com/api-reference/entity/items/entity-item-update.html',
        'Update Storage Item'
    )]
    public function update(string $entity, int $id, array $fields): UpdatedItemResult
    {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');
        return new UpdatedItemResult(
            $this->core->call(
                'entity.item.update',
                array_merge(
                    [
                        'ENTITY' => $entity,
                        'ID' => $id,
                    ],
                    $fields
                ),
            )
        );
    }
}