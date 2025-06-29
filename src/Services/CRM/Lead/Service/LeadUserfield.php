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

namespace Bitrix24\SDK\Services\CRM\Lead\Service;

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
use Bitrix24\SDK\Services\CRM\Lead\Result\LeadUserfieldResult;
use Bitrix24\SDK\Services\CRM\Lead\Result\LeadUserfieldsResult;
use Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNameIsTooLongException;
use Bitrix24\SDK\Services\CRM\Userfield\Service\UserfieldConstraints;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class LeadUserfield extends AbstractService
{
    public function __construct(private readonly UserfieldConstraints $userfieldConstraints, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Returns list of user lead fields by filter.
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
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/userfield/crm-lead-userfield-list.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.userfield.list',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/userfield/crm-lead-userfield-list.html',
        'Returns list of user lead fields by filter.'
    )]
    public function list(array $order, array $filter): LeadUserfieldsResult
    {
        return new LeadUserfieldsResult(
            $this->core->call(
                'crm.lead.userfield.list',
                [
                    'order'  => $order,
                    'filter' => $filter,
                ]
            )
        );
    }

    /**
     * Created new user field for leads.
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
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/userfield/crm-lead-userfield-add.html
     *
     */
    #[ApiEndpointMetadata(
        'crm.lead.userfield.add',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/userfield/crm-lead-userfield-add.html',
        'Created new user field for leads.'
    )]
    public function add(array $userfieldItemFields): AddedItemResult
    {
        $this->userfieldConstraints->validName($userfieldItemFields['FIELD_NAME']);

        return new AddedItemResult(
            $this->core->call(
                'crm.lead.userfield.add',
                [
                    'fields' => $userfieldItemFields,
                ]
            )
        );
    }

    /**
     * Deleted userfield for leads
     *
     *
     * @throws BaseException
     * @throws TransportException
     * @link  https://apidocs.bitrix24.com/api-reference/crm/leads/userfield/crm-lead-userfield-delete.html
     *
     */
    #[ApiEndpointMetadata(
        'crm.lead.userfield.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/userfield/crm-lead-userfield-delete.html',
        'Deleted userfield for leads'
    )]
    public function delete(int $userfieldId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.lead.userfield.delete',
                [
                    'id' => $userfieldId,
                ]
            )
        );
    }

    /**
     * Returns a userfield for lead by ID.
     *
     *
     * @throws BaseException
     * @throws TransportException
     * @link  https://apidocs.bitrix24.com/api-reference/crm/leads/userfield/crm-lead-userfield-get.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.userfield.get',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/userfield/crm-lead-userfield-get.html',
        'Returns a userfield for lead by ID.'
    )]
    public function get(int $userfieldItemId): LeadUserfieldResult
    {
        return new LeadUserfieldResult(
            $this->core->call(
                'crm.lead.userfield.get',
                [
                    'id' => $userfieldItemId,
                ]
            )
        );
    }

    /**
     * Updates an existing user field for leads.
     *
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/userfield/crm-lead-userfield-update.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.userfield.update',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/userfield/crm-lead-userfield-update.html',
        'Updates an existing user field for leads.'
    )]
    public function update(int $userfieldItemId, array $userfieldFieldsToUpdate): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.lead.userfield.update',
                [
                    'id'     => $userfieldItemId,
                    'fields' => $userfieldFieldsToUpdate,
                ]
            )
        );
    }
}
