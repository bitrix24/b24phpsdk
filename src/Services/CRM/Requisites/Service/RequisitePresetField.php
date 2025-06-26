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

namespace Bitrix24\SDK\Services\CRM\Requisites\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisitePresetFieldResult;
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisitePresetFieldsResult;
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisitePresetAvailableFieldsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class RequisitePresetField extends AbstractService
{
    /**
     * Adds a customizable field to the requisites template
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/fields/crm-requisite-preset-field-add.html
     *
     * @param array{
     *   FIELD_NAME?: string,
     *   FIELD_TITLE?: string,
     *   SORT?: int,
     *   IN_SHORT_LIST?: string,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.preset.field.add',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/fields/crm-requisite-preset-field-add.html',
        'Adds a customizable field to the requisites template'
    )]
    public function add(int $presetId, array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.requisite.preset.field.add',
                [
                    'preset'  => ['ID' => $presetId],
                    'fields' => $fields
                ]
            )
        );
    }

    /**
     * Deletes a customizable field from the requisites template.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/fields/crm-requisite-preset-field-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.preset.field.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/fields/crm-requisite-preset-field-delete.html',
        'Deletes a customizable field from the requisites template'
    )]
    public function delete(int $id, int $presetId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.requisite.preset.field.delete',
                [
                    'id' => $id,
                    'preset'  => ['ID' => $presetId],
                ]
            )
        );  // ###
    }

    /**
     * Returns a formal description of the fields describing the custom field in the requisites template.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/fields/crm-requisite-preset-field-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.preset.field.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/fields/crm-requisite-preset-field-fields.html',
        'Returns a formal description of the fields describing the custom field in the requisites template'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.requisite.preset.field.fields'));
    }

    /**
     * Returns the description of the custom field in the requisites template by identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/fields/crm-requisite-preset-field-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.preset.field.get',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/fields/crm-requisite-preset-field-get.html',
        'Returns the description of the custom field in the requisites template by identifier'
    )]
    public function get(int $id, int $presetId): RequisitePresetFieldResult
    {
        return new RequisitePresetFieldResult(
            $this->core->call(
                'crm.requisite.preset.field.get',
                [
                    'id' => $id,
                    'preset'  => ['ID' => $presetId],
                ]
            )
        );
    }

    /**
     * Returns a list of all custom fields for a specific requisites template.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/fields/crm-requisite-preset-field-list.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.preset.field.list',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/fields/crm-requisite-preset-field-list.html',
        'Returns a list of all custom fields for a specific requisites template'
    )]
    public function list(int $presetId): RequisitePresetFieldsResult
    {
        return new RequisitePresetFieldsResult(
            $this->core->call(
                'crm.requisite.preset.field.list',
                [
                    'preset'  => ['ID' => $presetId],
                ]
            )
        );
    }

    /**
     * Modifies a custom field in the requisites template.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/fields/crm-requisite-preset-field-update.html
     *
     * @param array{
     *   FIELD_NAME?: string,
     *   FIELD_TITLE?: string,
     *   SORT?: int,
     *   IN_SHORT_LIST?: string,
     * } $fields
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.preset.field.update',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/fields/crm-requisite-preset-field-update.html',
        'Modifies a custom field in the requisites template'
    )]
    public function update(int $id, int $presetId, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.requisite.preset.field.update',
                [
                    'id' => $id,
                    'preset'  => ['ID' => $presetId],
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Returns fields available for addition to the specified requisites template.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/fields/crm-requisite-preset-field-available-to-add.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.preset.field.availabletoadd',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/fields/crm-requisite-preset-field-available-to-add.html',
        'Returns fields available for addition to the specified requisites template'
    )]
    public function availabletoadd(int $presetId): RequisitePresetAvailableFieldsResult
    {
        return new RequisitePresetAvailableFieldsResult(
            $this->core->call(
                'crm.requisite.preset.field.availabletoadd',
                [
                    'preset'  => ['ID' => $presetId],
                ]
            )
        );
    }

}
