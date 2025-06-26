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
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisiteUserfieldResult;
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisiteUserfieldsResult;
use Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNameIsTooLongException;
use Bitrix24\SDK\Services\CRM\Userfield\Service\UserfieldConstraints;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class RequisiteUserfield extends AbstractService
{
    public function __construct(private readonly UserfieldConstraints $userfieldConstraints, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Returns a list of custom fields for requisites by filter.
     *
     * @param array{
     *   ID?: string,
     *   ENTITY_ID?: string,
     *   FIELD_NAME?: string,
     *   USER_TYPE_ID?: string,
     *   XML_ID?: string,
     *   SORT?: string,
     *   MULTIPLE?: string,
     *   MANDATORY?: string,
     *   SHOW_FILTER?: string,
     *   SHOW_IN_LIST?: string,
     *   EDIT_IN_LIST?: string,
     *   IS_SEARCHABLE?: string,
     *   EDIT_FORM_LABEL?: string,
     *   LIST_COLUMN_LABEL?: string,
     *   LIST_FILTER_LABEL?: string,
     *   ERROR_MESSAGE?: string,
     *   HELP_MESSAGE?: string,
     *   LIST?: string,
     *   SETTINGS?: string,
     *   } $order
     * @param array{
     *   ID?: string,
     *   ENTITY_ID?: string,
     *   FIELD_NAME?: string,
     *   USER_TYPE_ID?: string,
     *   XML_ID?: string,
     *   SORT?: string,
     *   MULTIPLE?: string,
     *   MANDATORY?: string,
     *   SHOW_FILTER?: string,
     *   SHOW_IN_LIST?: string,
     *   EDIT_IN_LIST?: string,
     *   IS_SEARCHABLE?: string,
     *   EDIT_FORM_LABEL?: string,
     *   LIST_COLUMN_LABEL?: string,
     *   LIST_FILTER_LABEL?: string,
     *   ERROR_MESSAGE?: string,
     *   HELP_MESSAGE?: string,
     *   LIST?: string,
     *   SETTINGS?: string,
     *   } $filter
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/user-fields/crm-requisite-userfield-list.html
     */
    #[ApiEndpointMetadata(
        'crm.requisite.userfield.list',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/user-fields/crm-requisite-userfield-list.html',
        'Returns a list of custom fields for requisites by filter'
    )]
    public function list(array $order, array $filter): RequisiteUserfieldsResult
    {
        return new RequisiteUserfieldsResult(
            $this->core->call(
                'crm.requisite.userfield.list',
                [
                    'order'  => $order,
                    'filter' => $filter,
                ]
            )
        );
    }

    /**
     * Creates a new custom field for a requisite.
     *
     * System limitation for field name - 20 characters.
     * Prefix UF_CRM_is always added to the user field name.
     * As a result, the actual name length - 13 characters.
     *
     * @param array{
     *   FIELD_NAME?: string,
     *   USER_TYPE_ID?: string,
     *   XML_ID?: string,
     *   SORT?: string,
     *   MULTIPLE?: string,
     *   MANDATORY?: string,
     *   SHOW_FILTER?: string,
     *   SHOW_IN_LIST?: string,
     *   EDIT_IN_LIST?: string,
     *   IS_SEARCHABLE?: string,
     *   EDIT_FORM_LABEL?: string|array,
     *   LIST_COLUMN_LABEL?: string|array,
     *   LIST_FILTER_LABEL?: string|array,
     *   ERROR_MESSAGE?: string,
     *   HELP_MESSAGE?: string,
     *   LIST?: string,
     *   SETTINGS?: array,
     *   } $userfieldItemFields
     *
     * @throws BaseException
     * @throws TransportException
     * @throws UserfieldNameIsTooLongException
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/user-fields/crm-requisite-userfield-add.html
     *
     */
    #[ApiEndpointMetadata(
        'crm.requisite.userfield.add',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/user-fields/crm-requisite-userfield-add.html',
        'Creates a new custom field for a requisite.'
    )]
    public function add(array $userfieldItemFields): AddedItemResult
    {
        $this->userfieldConstraints->validName($userfieldItemFields['FIELD_NAME']);

        return new AddedItemResult(
            $this->core->call(
                'crm.requisite.userfield.add',
                [
                    'fields' => $userfieldItemFields,
                ]
            )
        );
    }

    /**
     * Deletes a custom field for a requisite
     *
     *
     * @throws BaseException
     * @throws TransportException
     * @link  https://apidocs.bitrix24.com/api-reference/crm/requisites/user-fields/crm-requisite-userfield-delete.html
     *
     */
    #[ApiEndpointMetadata(
        'crm.requisite.userfield.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/user-fields/crm-requisite-userfield-delete.html',
        'Deletes a custom field for a requisite'
    )]
    public function delete(int $userfieldId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.requisite.userfield.delete',
                [
                    'id' => $userfieldId,
                ]
            )
        );
    }

    /**
     * Returns a custom field for a requisite by identifier.
     *
     *
     * @throws BaseException
     * @throws TransportException
     * @link  https://apidocs.bitrix24.com/api-reference/crm/requisites/user-fields/crm-requisite-userfield-get.html
     */
    #[ApiEndpointMetadata(
        'crm.requisite.userfield.get',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/user-fields/crm-requisite-userfield-get.html',
        'Returns a custom field for a requisite by identifier'
    )]
    public function get(int $userfieldItemId): RequisiteUserfieldResult
    {
        return new RequisiteUserfieldResult(
            $this->core->call(
                'crm.requisite.userfield.get',
                [
                    'id' => $userfieldItemId,
                ]
            )
        );
    }

    /**
     * Modifies an existing custom field for a requisite.
     *
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/user-fields/crm-requisite-userfield-update.html
     */
    #[ApiEndpointMetadata(
        'crm.requisite.userfield.update',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/user-fields/crm-requisite-userfield-update.html',
        'Modifies an existing custom field for a requisite.'
    )]
    public function update(int $userfieldItemId, array $userfieldFieldsToUpdate): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.requisite.userfield.update',
                [
                    'id'     => $userfieldItemId,
                    'fields' => $userfieldFieldsToUpdate,
                ]
            )
        );
    }
}
