<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Lists\Field\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Lists\Field\Result\AddedFieldResult;
use Bitrix24\SDK\Services\Lists\Field\Result\FieldResult;
use Bitrix24\SDK\Services\Lists\Field\Result\FieldsResult;
use Bitrix24\SDK\Services\Lists\Field\Result\FieldTypesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['lists']))]
class Field extends AbstractService
{
    /**
     * Field constructor.
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Creates a field for the universal list.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/fields/lists-field-add.html
     *
     * @param string $iblockTypeId Information block type identifier (lists, bitrix_processes, lists_socnet)
     * @param int|null $iblockId Information block identifier
     * @param string|null $iblockCode Symbolic code of the information block
     * @param array $fields Array of field parameters
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.field.add',
        'https://apidocs.bitrix24.com/api-reference/lists/fields/lists-field-add.html',
        'Creates a field for the universal list.'
    )]
    public function add(
        string $iblockTypeId,
        array $fields,
        ?int $iblockId = null,
        ?string $iblockCode = null
    ): AddedFieldResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
            'FIELDS' => $fields,
        ];

        if ($iblockId !== null) {
            $params['IBLOCK_ID'] = $iblockId;
        }

        if ($iblockCode !== null) {
            $params['IBLOCK_CODE'] = $iblockCode;
        }

        return new AddedFieldResult(
            $this->core->call('lists.field.add', $params)
        );
    }

    /**
     * Updates a field of the universal list.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/fields/lists-field-update.html
     *
     * @param string $iblockTypeId Information block type identifier
     * @param string $fieldId Field identifier
     * @param array $fields Array of field parameters to update
     * @param int|null $iblockId Information block identifier
     * @param string|null $iblockCode Symbolic code of the information block
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.field.update',
        'https://apidocs.bitrix24.com/api-reference/lists/fields/lists-field-update.html',
        'Updates a field of the universal list.'
    )]
    public function update(
        string $iblockTypeId,
        string $fieldId,
        array $fields,
        ?int $iblockId = null,
        ?string $iblockCode = null
    ): UpdatedItemResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
            'FIELD_ID' => $fieldId,
            'FIELDS' => $fields,
        ];

        if ($iblockId !== null) {
            $params['IBLOCK_ID'] = $iblockId;
        }

        if ($iblockCode !== null) {
            $params['IBLOCK_CODE'] = $iblockCode;
        }

        return new UpdatedItemResult(
            $this->core->call('lists.field.update', $params)
        );
    }

    /**
     * Returns data about a field or list of fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/fields/lists-field-get.html
     *
     * @param string $iblockTypeId Information block type identifier
     * @param int|null $iblockId Information block identifier
     * @param string|null $iblockCode Symbolic code of the information block
     * @param string|null $fieldId Field identifier (if not specified, returns all fields)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.field.get',
        'https://apidocs.bitrix24.com/api-reference/lists/fields/lists-field-get.html',
        'Returns data about a field or list of fields.'
    )]
    public function get(
        string $iblockTypeId,
        ?int $iblockId = null,
        ?string $iblockCode = null,
        ?string $fieldId = null
    ): FieldResult|FieldsResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
        ];

        if ($iblockId !== null) {
            $params['IBLOCK_ID'] = $iblockId;
        }

        if ($iblockCode !== null) {
            $params['IBLOCK_CODE'] = $iblockCode;
        }

        if ($fieldId !== null) {
            $params['FIELD_ID'] = $fieldId;
        }

        $response = $this->core->call('lists.field.get', $params);

        // If specific field ID requested, return single field result
        if ($fieldId !== null) {
            return new FieldResult($response);
        }

        // Otherwise return multiple fields result
        return new FieldsResult($response);
    }

    /**
     * Deletes a field from the universal list.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/fields/lists-field-delete.html
     *
     * @param string $iblockTypeId Information block type identifier
     * @param string $fieldId Field identifier to delete
     * @param int|null $iblockId Information block identifier
     * @param string|null $iblockCode Symbolic code of the information block
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.field.delete',
        'https://apidocs.bitrix24.com/api-reference/lists/fields/lists-field-delete.html',
        'Deletes a field from the universal list.'
    )]
    public function delete(
        string $iblockTypeId,
        string $fieldId,
        ?int $iblockId = null,
        ?string $iblockCode = null
    ): DeletedItemResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
            'FIELD_ID' => $fieldId,
        ];

        if ($iblockId !== null) {
            $params['IBLOCK_ID'] = $iblockId;
        }

        if ($iblockCode !== null) {
            $params['IBLOCK_CODE'] = $iblockCode;
        }

        return new DeletedItemResult(
            $this->core->call('lists.field.delete', $params)
        );
    }

    /**
     * Returns a list of available field types for the list.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/fields/lists-field-type-get.html
     *
     * @param string $iblockTypeId Information block type identifier
     * @param int|null $iblockId Information block identifier
     * @param string|null $iblockCode Symbolic code of the information block
     * @param int|null $fieldId Field identifier (optional)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.field.type.get',
        'https://apidocs.bitrix24.com/api-reference/lists/fields/lists-field-type-get.html',
        'Returns a list of available field types for the list.'
    )]
    public function types(
        string $iblockTypeId,
        ?int $iblockId = null,
        ?string $iblockCode = null,
        ?int $fieldId = null
    ): FieldTypesResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
        ];

        if ($iblockId !== null) {
            $params['IBLOCK_ID'] = $iblockId;
        }

        if ($iblockCode !== null) {
            $params['IBLOCK_CODE'] = $iblockCode;
        }

        if ($fieldId !== null) {
            $params['FIELD_ID'] = $fieldId;
        }

        return new FieldTypesResult(
            $this->core->call('lists.field.type.get', $params)
        );
    }

    /**
     * Helper method: Create field by iblock code
     */
    public function addByCode(string $iblockTypeId, string $iblockCode, array $fields): AddedFieldResult
    {
        return $this->add($iblockTypeId, $fields, null, $iblockCode);
    }

    /**
     * Helper method: Update field by iblock code
     */
    public function updateByCode(
        string $iblockTypeId,
        string $iblockCode,
        string $fieldId,
        array $fields
    ): UpdatedItemResult {
        return $this->update($iblockTypeId, $fieldId, $fields, null, $iblockCode);
    }

    /**
     * Helper method: Get field(s) by iblock code
     */
    public function getByCode(
        string $iblockTypeId,
        string $iblockCode,
        ?string $fieldId = null
    ): FieldResult|FieldsResult {
        return $this->get($iblockTypeId, null, $iblockCode, $fieldId);
    }

    /**
     * Helper method: Delete field by iblock code
     */
    public function deleteByCode(string $iblockTypeId, string $iblockCode, string $fieldId): DeletedItemResult
    {
        return $this->delete($iblockTypeId, $fieldId, null, $iblockCode);
    }
}
