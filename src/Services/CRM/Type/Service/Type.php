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

namespace Bitrix24\SDK\Services\CRM\Type\Service;

use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Services\CRM\Type\Result\AddedTypeItemResult;
use Bitrix24\SDK\Services\CRM\Type\Result\DeletedItemResult;
use Bitrix24\SDK\Services\CRM\Type\Result\TypeItemResult;
use Bitrix24\SDK\Services\CRM\Type\Result\TypeResult;
use Bitrix24\SDK\Services\CRM\Type\Result\TypesResult;
use Bitrix24\SDK\Services\CRM\Type\Result\UpdatedTypeItemResult;

#[ApiServiceMetadata(new Scope(['crm']))]
class Type extends AbstractService
{
    /**
     * This method retrieves information about the custom fields of the smart process settings.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/user-defined-object-types/crm-type-fields.html
     *
     * @return FieldsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.type.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/user-defined-object-types/crm-type-fields.html',
        'This method retrieves information about the custom fields of the smart process settings.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.type.fields'));
    }

    /**
     * Create a new custom type crm.type.add
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/user-defined-object-types/crm-type-add.html
     *
     * @param string $title
     * @param int|null $entityTypeId
     * @param array $parameters
     * @return AddedTypeItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.type.add',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/user-defined-object-types/crm-type-add.html',
        'This method creates a new SPA.'
    )]
    public function add(string $title, ?int $entityTypeId = null, array $parameters = []): AddedTypeItemResult
    {
        $fields = array_merge(['title' => $title], $parameters);
        if ($entityTypeId !== null) {
            $fields['entityTypeId'] = $entityTypeId;
        }

        return new AddedTypeItemResult($this->core->call('crm.type.add', [
            'fields' => $fields,
        ]));
    }

    /**
     * This method updates an existing SPA by its identifier id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/user-defined-object-types/crm-type-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.type.update',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/user-defined-object-types/crm-type-update.html',
        'This method updates an existing SPA by its identifier id.'
    )]
    public function update(int $id, array $fields): UpdatedTypeItemResult
    {
        return new UpdatedTypeItemResult($this->core->call('crm.type.update', [
            'id' => $id,
            'fields' => $fields,
        ]));
    }

    /**
     * The method retrieves information about the SPA with the identifier id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/user-defined-object-types/crm-type-get.html
     *
     * @return TypeResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.type.get',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/user-defined-object-types/crm-type-get.html',
        'The method retrieves information about the SPA with the identifier id.'
    )]
    public function get(int $id): TypeResult
    {
        return new TypeResult($this->core->call('crm.type.get', ['id' => $id]));
    }

    /**
     * The method retrieves information about the SPA with the smart process type identifier entityTypeId.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/user-defined-object-types/crm-type-get-by-entity-type-id.html
     *
     * @param int $entityTypeId
     * @return TypeResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.type.getByEntityTypeId',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/user-defined-object-types/crm-type-get-by-entity-type-id.html',
        'The method retrieves information about the SPA with the smart process type identifier entityTypeId.'
    )]
    public function getByEntityTypeId(int $entityTypeId): TypeResult
    {
        return new TypeResult($this->core->call('crm.type.getByEntityTypeId', ['entityTypeId' => $entityTypeId]));
    }

    /**
     * Get a list of custom types crm.type.list
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/user-defined-object-types/crm-type-get-by-entity-type-id.html
     *
     * @param array $order
     * @param array $filter
     * @param int $start
     * @return TypesResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.type.list',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/user-defined-object-types/crm-type-list.html',
        'Get a list of custom types crm.type.list'
    )]
    public function list(array $order = [], array $filter = [], int $start = 0): TypesResult
    {
        return new TypesResult($this->core->call('crm.type.list', [
            'order' => $order,
            'filter' => $filter,
            'start' => $start,
        ]));
    }

    /**
     * This method deletes an existing smart process by the identifier id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/user-defined-object-types/crm-type-delete.html
     *
     * @param int $entityTypeId
     * @return DeletedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.type.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/user-defined-object-types/crm-type-delete.html',
        'This method deletes an existing smart process by the identifier id.'
    )]
    public function delete(int $entityTypeId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('crm.type.delete', ['entityTypeId' => $entityTypeId]));
    }
}
