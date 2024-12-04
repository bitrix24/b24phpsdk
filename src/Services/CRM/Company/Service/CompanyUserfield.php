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

namespace Bitrix24\SDK\Services\CRM\Company\Service;

use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Company\Result\CompanyUserfieldResult;
use Bitrix24\SDK\Services\CRM\Company\Result\CompanyUserfieldsResult;
use Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNameIsTooLongException;
use Bitrix24\SDK\Services\CRM\Userfield\Service\UserfieldConstraints;
use Psr\Log\LoggerInterface;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;

#[ApiServiceMetadata(new Scope(['crm']))]
class CompanyUserfield extends AbstractService
{
    private UserfieldConstraints $userfieldConstraints;

    public function __construct(UserfieldConstraints $userfieldConstraints, CoreInterface $core, LoggerInterface $log)
    {
        $this->userfieldConstraints = $userfieldConstraints;
        parent::__construct($core, $log);
    }

    /**
     * Created new user field for company.
     *
     * System limitation for field name - 20 characters.
     * Prefix UF_CRM_is always added to the user field name.
     * As a result, the actual name length - 13 characters.
     *
     * @param array{
     *   FIELD_NAME: string,
     *   USER_TYPE_ID: string,
     *   XML_ID: string,
     *   SORT: string,
     *   MULTIPLE: string,
     *   MANDATORY: string,
     *   SHOW_FILTER: string,
     *   SHOW_IN_LIST: string,
     *   EDIT_IN_LIST: string,
     *   IS_SEARCHABLE: string,
     *   EDIT_FORM_LABEL: string,
     *   LIST_COLUMN_LABEL: string,
     *   LIST_FILTER_LABEL: string,
     *   ERROR_MESSAGE: string,
     *   HELP_MESSAGE: string,
     *   LIST?: array,
     *   SETTINGS?: array,
     *   } $userfieldItemFields
     *
     * @return AddedItemResult
     * @throws BaseException
     * @throws TransportException
     * @throws UserfieldNameIsTooLongException
     * @link https://apidocs.bitrix24.com/api-reference/crm/companies/userfields/crm-company-userfield-add.html
     *
     */
    #[ApiEndpointMetadata(
        'crm.company.userfield.add',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/userfields/crm-company-userfield-add.html',
        'The method crm.company.userfield.add creates a new custom field for companies.'
    )]
    public function add(array $userfieldItemFields): AddedItemResult
    {
        $this->userfieldConstraints->validName($userfieldItemFields['FIELD_NAME']);
        return new AddedItemResult(
            $this->core->call(
                'crm.company.userfield.add',
                [
                    'fields' => $userfieldItemFields,
                ]
            )
        );
    }

    /**
     * Get Custom Company Field by ID
     *
     * @param int $userfieldItemId
     *
     * @return CompanyUserfieldResult
     * @throws BaseException
     * @throws TransportException
     * @link  https://apidocs.bitrix24.com/api-reference/crm/companies/userfields/crm-company-userfield-get.html
     */
    #[ApiEndpointMetadata(
        'crm.company.userfield.get',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/userfields/crm-company-userfield-get.html',
        'Get Custom Company Field by ID'
    )]
    public function get(int $userfieldItemId): CompanyUserfieldResult
    {
        return new CompanyUserfieldResult(
            $this->core->call(
                'crm.company.userfield.get',
                [
                    'id' => $userfieldItemId,
                ]
            )
        );
    }

    /**
     * The method crm.company.userfield.list returns a list of custom company fields based on the filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/companies/userfields/crm-company-userfield-list.html
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
     * @return CompanyUserfieldsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.company.userfield.list',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/userfields/crm-company-userfield-list.html',
        'The method crm.company.userfield.list returns a list of custom company fields based on the filter.'
    )]
    public function list(array $order = [], array $filter = []): CompanyUserfieldsResult
    {
        return new CompanyUserfieldsResult(
            $this->core->call('crm.company.userfield.list', ['order' => $order, 'filter' => $filter])
        );
    }

    /**
     * Delete Custom Field for Companies
     *
     * @param int $userfieldId
     *
     * @return DeletedItemResult
     * @throws BaseException
     * @throws TransportException
     * @link  https://apidocs.bitrix24.com/api-reference/crm/companies/userfields/crm-company-userfield-delete.html
     *
     */
    #[ApiEndpointMetadata(
        'crm.company.userfield.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/userfields/crm-company-userfield-delete.html',
        'Delete Custom Field for Companies'
    )]
    public function delete(int $userfieldId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.company.userfield.delete',
                [
                    'id' => $userfieldId,
                ]
            )
        );
    }

    /**
     * Update Existing Custom Field for Companies
     *
     * @param int $userfieldItemId
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
     *   } $userfieldFieldsToUpdate
     * @param ?array $list
     * @return \Bitrix24\SDK\Core\Result\UpdatedItemResult
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/crm/companies/userfields/crm-company-userfield-update.html
     */
    #[ApiEndpointMetadata(
        'crm.company.userfield.update',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/userfields/crm-company-userfield-update.html',
        'Update Existing Custom Field for Companies'
    )]
    public function update(int $userfieldItemId, array $userfieldFieldsToUpdate, ?array $list = null): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.company.userfield.update',
                [
                    'id' => $userfieldItemId,
                    'fields' => $userfieldFieldsToUpdate,
                    'LIST' => $list,
                ]
            )
        );
    }
}