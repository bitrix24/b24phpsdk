<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Entity\Section\Service;

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
use Bitrix24\SDK\Services\Entity\Section\Result\SectionsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['entity']))]
class Section extends AbstractService
{
    public function __construct(
        public Batch $batch,
        CoreInterface $core,
        LoggerInterface $logger
    ) {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a storage section
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/sections/entity-section-add.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.section.add',
        'https://apidocs.bitrix24.com/api-reference/entity/sections/entity-section-add.html',
        'Adds a storage section'
    )]
    public function add(string $entity, string $name, array $fields): AddedItemResult
    {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');
        $this->guardNonEmptyString($name, 'section name must be an non empty string');

        return new AddedItemResult(
            $this->core->call(
                'entity.section.add',
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
     * Retrieves a list of storage sections
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/sections/entity-section-get.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.section.get',
        'https://apidocs.bitrix24.com/api-reference/entity/sections/entity-section-get.html',
        'Retrieves a list of storage sections'
    )]
    public function get(string $entity, array $sort = [], array $filter = [], int $startItem = 0): SectionsResult
    {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');

        return new SectionsResult(
            $this->core->call(
                'entity.section.get',
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
     * Deletes a storage section
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/sections/entity-section-delete.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.section.delete',
        'https://apidocs.bitrix24.com/api-reference/entity/sections/entity-section-delete.html',
        'Deletes a storage section'
    )]
    public function delete(string $entity, int $sectionId): DeletedItemResult
    {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');

        return new DeletedItemResult(
            $this->core->call(
                'entity.section.delete',
                [
                    'ENTITY' => $entity,
                    'ID' => $sectionId,
                ],
            )
        );
    }

    /**
     * Modifies a storage section
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/sections/entity-section-update.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.section.update',
        'https://apidocs.bitrix24.com/api-reference/entity/sections/entity-section-update.html',
        'Modifies a storage section'
    )]
    public function update(string $entity, int $id, array $fields): UpdatedItemResult
    {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');
        return new UpdatedItemResult(
            $this->core->call(
                'entity.section.update',
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
