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

namespace Bitrix24\SDK\Services\Entity\Item\Property\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Entity\Item\Property\Result\PropertiesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['entity']))]
class Property extends AbstractService
{
    public function __construct(
        public Batch $batch,
        CoreInterface $core,
        LoggerInterface $logger
    ) {
        parent::__construct($core, $logger);
    }

    /**
     * Add an additional property to storage elements.
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/items/properties/entity-item-property-add.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.item.property.add',
        'https://apidocs.bitrix24.com/api-reference/entity/items/properties/entity-item-property-add.html',
        'Add an additional property to storage elements.'
    )]
    public function add(string $entity, string $propertyCode, string $name, string $type): UpdatedItemResult
    {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');
        $this->guardNonEmptyString($propertyCode, 'entity must be an non empty string');
        $this->guardNonEmptyString($name, 'section name must be an non empty string');
        $this->guardNonEmptyString($type, 'entity must be an non empty string');

        return new UpdatedItemResult(
            $this->core->call(
                'entity.item.property.add',
                [
                    'ENTITY' => $entity,
                    'PROPERTY' => $propertyCode,
                    'NAME' => $name,
                    'TYPE' => $type,
                ]
            )
        );
    }

    /**
     * Retrieve a list of additional properties of storage elements.
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/items/properties/entity-item-property-get.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.item.property.get',
        'https://apidocs.bitrix24.com/api-reference/entity/items/properties/entity-item-property-get.html',
        'Retrieve a list of additional properties of storage elements.'
    )]
    public function get(string $entity, string $propertyCode=''): PropertiesResult
    {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');
        $param = [
            'ENTITY' => $entity,
        ]; 
        if ($propertyCode !== '') {
            $param['PROPERTY'] = $propertyCode;
        }
        return new PropertiesResult(
            $this->core->call(
                'entity.item.property.get',
                $param
            )
        );
    }

    /**
     * Delete an additional property of storage elements.
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/items/properties/entity-item-property-delete.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.item.property.delete',
        'https://apidocs.bitrix24.com/api-reference/entity/items/properties/entity-item-property-delete.html',
        'Delete an additional property of storage elements.'
    )]
    public function delete(string $entity, string $propertyCode): DeletedItemResult
    {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');
        $this->guardNonEmptyString($propertyCode, 'propery code must be an non empty string');

        return new DeletedItemResult(
            $this->core->call(
                'entity.item.property.delete',
                [
                    'ENTITY' => $entity,
                    'PROPERTY' => $propertyCode,
                ],
            )
        );
    }

    /**
     * Update an additional property of storage elements.
     *
     * @see https://apidocs.bitrix24.com/api-reference/entity/items/properties/entity-item-property-update.html
     * 
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'entity.item.property.update',
        'https://apidocs.bitrix24.com/api-reference/entity/items/properties/entity-item-property-update.html',
        'Update an additional property of storage elements.'
    )]
    public function update(string $entity, string $propertyCode, array $fields): UpdatedItemResult
    {
        $this->guardNonEmptyString($entity, 'entity must be an non empty string');
        $this->guardNonEmptyString($propertyCode, 'property code must be an non empty string');
        
        return new UpdatedItemResult(
            $this->core->call(
                'entity.item.property.update',
                array_merge(
                    [
                        'ENTITY' => $entity,
                        'PROPERTY' => $propertyCode,
                    ],
                    $fields
                ),
            )
        );
    }
}
