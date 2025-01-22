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

namespace Bitrix24\SDK\Services\Entity\Entity\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Entity\Entity\Result\AddedEntityResult;
use Bitrix24\SDK\Services\Entity\Entity\Result\EntitiesResult;
use Bitrix24\SDK\Services\Entity\Entity\Result\EntityRightsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['entity']))]
class Entity extends AbstractService
{
    public function __construct(
        public Batch $batch,
        CoreInterface $core,
        LoggerInterface $logger
    ) {
        parent::__construct($core, $logger);
    }

    /**
     * Create Data Storage
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/entities/entity-add.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.add',
        'https://apidocs.bitrix24.com/api-reference/entity/entities/entity-add.html',
        'Create Data Storage'
    )]
    public function add(string $entity, string $name, array $access): AddedEntityResult
    {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');
        $this->guardNonEmptyString($name, 'entity name must be an non empty string');

        return new AddedEntityResult(
            $this->core->call(
                'entity.add',
                [
                    'ENTITY' => $entity,
                    'NAME' => $name,
                    'ACCESS' => $access,
                ]
            )
        );
    }

    /**
     * Change Parameters
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/entities/entity-update.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.update',
        'https://apidocs.bitrix24.com/api-reference/entity/entities/entity-update.html',
        'Change Parameters'
    )]
    public function update(
        string $entity,
        ?string $name = null,
        ?array $access = null,
        ?string $updatedEntity = null
    ): UpdatedItemResult {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');

        return new UpdatedItemResult(
            $this->core->call(
                'entity.update',
                [
                    'ENTITY' => $entity,
                    'NAME' => $name,
                    'ACCESS' => $access,
                    'ENTITY_NEW' => $updatedEntity,
                ]
            )
        );
    }

    /**
     * Delete Storage entity.delete
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/entities/entity-delete.html
     * @throws TransportException
     * @throws InvalidArgumentException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.delete',
        'https://apidocs.bitrix24.com/api-reference/entity/entities/entity-delete.html',
        'Delete Storage'
    )]
    public function delete(string $entity): DeletedItemResult
    {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');

        return new DeletedItemResult(
            $this->core->call(
                'entity.delete',
                [
                    'ENTITY' => $entity,
                ]
            )
        );
    }

    /**
     * Get Storage Parameters or List of All Storages
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/entities/entity-get.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.get',
        'https://apidocs.bitrix24.com/api-reference/entity/entities/entity-get.html',
        'Get Storage Parameters or List of All Storages'
    )]
    public function get(?string $entity = null): EntitiesResult
    {
        return new EntitiesResult($this->core->call('entity.get', ['ENTITY' => $entity]));
    }

    /**
     * Get or Change Access Permissions
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/entities/entity-rights.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.get',
        'https://apidocs.bitrix24.com/api-reference/entity/entities/entity-rights.html',
        'Get or Change Access Permissions'
    )]
    public function rights(?string $entity = null, ?array $access = null): EntityRightsResult
    {
        return new EntityRightsResult(
            $this->core->call(
                'entity.rights',
                [
                    'ENTITY' => $entity,
                    'ACCESS' => $access
                ]
            )
        );
    }
}