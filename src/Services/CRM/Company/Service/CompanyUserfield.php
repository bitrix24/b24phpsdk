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
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Company\Result\CompanyUserfieldsResult;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealUserfieldResult;
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
     * The method crm.company.userfield.list returns a list of custom company fields based on the filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/companies/userfields/crm-company-userfield-list.html
     *
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
        return new CompanyUserfieldsResult($this->core->call('crm.company.userfield.list'));
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
     * Deleted userfield for deals
     *
     * @param int $userfieldId
     *
     * @return \Bitrix24\SDK\Core\Result\DeletedItemResult
     * @throws BaseException
     * @throws TransportException
     * @link  https://training.bitrix24.com/rest_help/crm/deals/crm_deal_userfield_delete.php
     *
     */
    #[ApiEndpointMetadata(
        'crm.deal.userfield.delete',
        'https://training.bitrix24.com/rest_help/crm/deals/crm_deal_userfield_delete.php',
        'Deleted userfield for deals'
    )]
    public function delete(int $userfieldId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.deal.userfield.delete',
                [
                    'id' => $userfieldId,
                ]
            )
        );
    }

    /**
     * Returns a userfield for deal by ID.
     *
     * @param int $userfieldItemId
     *
     * @return DealUserfieldResult
     * @throws BaseException
     * @throws TransportException
     * @link  https://training.bitrix24.com/rest_help/crm/deals/crm_deal_userfield_get.php
     */
    #[ApiEndpointMetadata(
        'crm.deal.userfield.get',
        'https://training.bitrix24.com/rest_help/crm/deals/crm_deal_userfield_get.php',
        'Returns a userfield for deal by ID.'
    )]
    public function get(int $userfieldItemId): DealUserfieldResult
    {
        return new DealUserfieldResult(
            $this->core->call(
                'crm.deal.userfield.get',
                [
                    'id' => $userfieldItemId,
                ]
            )
        );
    }

    /**
     * Updates an existing user field for deals.
     *
     * @param int $userfieldItemId
     * @param array $userfieldFieldsToUpdate
     *
     * @return \Bitrix24\SDK\Core\Result\UpdatedItemResult
     * @throws BaseException
     * @throws TransportException
     * @link https://training.bitrix24.com/rest_help/crm/deals/crm_deal_userfield_update.php
     */
    #[ApiEndpointMetadata(
        'crm.deal.userfield.update',
        'https://training.bitrix24.com/rest_help/crm/deals/crm_deal_userfield_update.php',
        'Updates an existing user field for deals.'
    )]
    public function update(int $userfieldItemId, array $userfieldFieldsToUpdate): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.deal.userfield.update',
                [
                    'id' => $userfieldItemId,
                    'fields' => $userfieldFieldsToUpdate,
                ]
            )
        );
    }
}