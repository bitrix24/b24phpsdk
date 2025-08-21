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

namespace Bitrix24\SDK\Services\Sale\PersonType\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Sale\PersonType\Result\PersonTypeFieldsResult;
use Bitrix24\SDK\Services\Sale\PersonType\Result\PersonTypeResult;
use Bitrix24\SDK\Services\Sale\PersonType\Result\PersonTypesResult;
use Bitrix24\SDK\Services\Sale\PersonType\Result\AddedPersonTypeResult;
use Bitrix24\SDK\Services\Sale\PersonType\Result\UpdatedPersonTypeResult;

#[ApiServiceMetadata(new Scope(['sale']))]
class PersonType extends AbstractService
{
    /**
     * Adds a payer type
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-add.html
     *
     * $param array {
     *  name,
     *  code,
     *  sort,
     *  active,
     *  xmlId,
     *  } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.persontype.add',
        'https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-add.html',
        'Adds a payer type'
    )]
    public function add(array $fields): AddedPersonTypeResult
    {
        return new AddedPersonTypeResult(
            $this->core->call(
                'sale.persontype.add',
                [
                    'fields' => $fields
                ]
            )
        );
    }

    /**
     * Deletes a payer type.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.persontype.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-delete.html',
        'Deletes a payer type.'
    )]
    public function delete(int $itemId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'sale.persontype.delete',
                [
                    'id' => $itemId,
                ]
            )
        );
    }

    /**
     * Returns the fields of the payer type.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.persontype.get',
        'https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-get.html',
        'Returns the fields of the payer type'
    )]
    public function get(int $itemId): PersonTypeResult
    {
        return new PersonTypeResult(
            $this->core->call(
                'sale.persontype.get',
                [
                    'id' => $itemId,
                ]
            )
        );
    }

    /**
     * Returns a list of payer types.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-list.html
     *
     * $param ?array {
     *  id,
     *  name,
     *  code,
     *  sort,
     *  active,
     *  xmlId,
     *  } $select
     * $param ?array {
     *  id,
     *  name,
     *  code,
     *  sort,
     *  active,
     *  xmlId,
     *  } $filter
     * $param ?array {
     *  id,
     *  name,
     *  code,
     *  sort,
     *  active,
     *  xmlId,
     *  } $order
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.persontype.list',
        'https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-list.html',
        'Returns a list of payer types.'
    )]
    public function list(?array $select = [], ?array $filter = [], ?array $order = [], int $start = 0): PersonTypesResult
    {
        return new PersonTypesResult(
            $this->core->call(
                'sale.persontype.list',
                [
                    'select' => $select,
                    'filter' => $filter,
                    'order' => $order,
                    'start' => $start,
                ]
            )
        );
    }

    /**
     * Modifies a payer type.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-update.html
     *
     * $param array {
     *  name,
     *  code,
     *  sort,
     *  active,
     *  xmlId,
     *  } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.persontype.update',
        'https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-update.html',
        'Modifies a payer type.'
    )]
    public function update(int $itemId, array $fields): UpdatedPersonTypeResult
    {
        return new UpdatedPersonTypeResult(
            $this->core->call(
                'sale.persontype.update',
                [
                    'id' => $itemId,
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Returns the fields of the payer type.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-get-fields.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.persontype.getFields',
        'https://apidocs.bitrix24.com/api-reference/sale/person-type/sale-person-type-get-fields.html',
        'Returns the fields of the payer type'
    )]
    public function getFields(): PersonTypeFieldsResult
    {
        return new PersonTypeFieldsResult(
            $this->core->call(
                'sale.persontype.getFields',
                [
                ]
            )
        );
    }
}
