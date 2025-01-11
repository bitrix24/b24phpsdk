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
use Bitrix24\SDK\Services\CRM\Lead\Result\LeadResult;
use Bitrix24\SDK\Services\CRM\Requisites\Result\CountriesResult;
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisitePresetResult;
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisitePresetsResult;
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisitesResult;
use Bitrix24\SDK\Services\CRM\Requisites\Service\Batch;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class RequisitePreset extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $log)
    {
        parent::__construct($core, $log);
    }

    /**
     * Get Description of the Fields of the Requisite Template
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/crm-requisite-preset-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.preset.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/crm-requisite-preset-fields.html',
        'Get Description of the Fields of the Requisite Template'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.requisite.preset.fields'));
    }

    /**
     * Get list of requisite items.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/crm-requisite-preset-list.html
     *
     * @param array $order - order items
     * @param array{
     *  ID?: int,
     *  ENTITY_TYPE_ID?: int,
     *  COUNTRY_ID?: int,
     *  NAME?: string,
     *  DATE_CREATE?: string,
     *  DATE_MODIFY?: string,
     *  CREATED_BY_ID?: string,
     *  MODIFY_BY_ID?: string,
     *  ACTIVE?: string,
     *  SORT?: int,
     *  XML_ID?: string,
     *  } $filter
     * @param array $select = ['ID','ENTITY_TYPE_ID','COUNTRY_ID','NAME','DATE_CREATE','DATE_MODIFY','CREATED_BY_ID','MODIFY_BY_ID','ACTIVE','SORT','XML_ID']
     * @param integer $startItem - entity number to start from (usually returned in 'next' field of previous 'crm.requisite.preset.list' API call)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.preset.list',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/crm-requisite-preset-list.html',
        'Get a List of Requisite Templates by filter'
    )]
    public function list(
        array $order = [],
        array $filter = [],
        array $select = [],
        int $startItem = 0
    ): RequisitePresetsResult {
        return new RequisitePresetsResult(
            $this->core->call(
                'crm.requisite.preset.list',
                [
                    'order' => $order,
                    'filter' => $filter,
                    'select' => $select,
                    'start' => $startItem,
                ]
            )
        );
    }

    /**
     * Add new requisite preset
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/crm-requisite-preset-add.html
     *
     * @return AddedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.preset.add',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/crm-requisite-preset-add.html',
        'Method adds new requisite preset'
    )]
    public function add(
        int $entityTypeId,
        int $countryId,
        string $name,
        array $fields
    ): AddedItemResult {
        return new AddedItemResult(
            $this->core->call(
                'crm.requisite.preset.add',
                [
                    'fields' => array_merge(
                        [
                            'ENTITY_TYPE_ID' => $entityTypeId,
                            'COUNTRY_ID' => $countryId,
                            'NAME' => $name
                        ],
                        $fields,
                    )
                ]
            )
        );
    }

    /**
     * Get a list of countries for the requisite preset
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/crm-requisite-preset-countries.html
     *
     * @return CountriesResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.preset.countries',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/crm-requisite-preset-add.html',
        'Get a list of countries for the requisite preset'
    )]
    public function countries(): CountriesResult
    {
        return new CountriesResult($this->core->call('crm.requisite.preset.countries'));
    }

    /**
     * Deletes the specified requisite template by id
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/crm-requisite-preset-delete.html
     *
     * @param int $id
     *
     * @return DeletedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.preset.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/crm-requisite-preset-delete.html',
        'Deletes the specified requisite template by id'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.requisite.preset.delete',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Get Requisite Template Fields by ID
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/crm-requisite-preset-get.html
     *
     * @param int $id
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.preset.get',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/crm-requisite-preset-get.html',
        'Get Requisite Template Fields by ID'
    )]
    public function get(int $id): RequisitePresetResult
    {
        return new RequisitePresetResult($this->core->call('crm.requisite.preset.get', ['id' => $id]));
    }

    /**
     * Update the Requisite Template
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/crm-requisite-preset-update.html
     *
     * @param int $id
     * @param array{
     *  ID?: int,
     *  ENTITY_TYPE_ID?: int,
     *  COUNTRY_ID?: int,
     *  NAME?: string,
     *  DATE_CREATE?: string,
     *  DATE_MODIFY?: string,
     *  CREATED_BY_ID?: string,
     *  MODIFY_BY_ID?: string,
     *  ACTIVE?: string,
     *  SORT?: int,
     *  XML_ID?: string,
     *  } $fields
     * @return UpdatedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.preset.update',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/presets/crm-requisite-preset-update.html',
        'Update the Requisite Template'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.requisite.preset.update',
                [
                    'id'     => $id,
                    'fields' => $fields,
                ]
            )
        );
    }
}
