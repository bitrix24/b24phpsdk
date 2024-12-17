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

namespace Bitrix24\SDK\Services\CRM\Contact\Service;

use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Common\CompanyConnection;
use Bitrix24\SDK\Services\CRM\Contact\Result\ContactCompanyConnectionResult;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;

#[ApiServiceMetadata(new Scope(['crm']))]
class ContactCompany extends AbstractService
{
    /**
     * Get Field Descriptions for Contact-Company
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/contacts/company/crm-contact-company-fields.html
     *
     * @return FieldsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.contact.company.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/contacts/company/crm-contact-company-fields.html',
        'Get Fields for Contact-Company'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.contact.company.fields'));
    }

    /**
     * Set a set of companies associated with the specified contact
     *
     * @param non-negative-int $contactId
     * @param CompanyConnection[] $companyConnections
     * @throws InvalidArgumentException
     * @link https://apidocs.bitrix24.com/api-reference/crm/contacts/company/crm-contact-company-items-set.html
     */
    #[ApiEndpointMetadata(
        'crm.contact.company.items.set',
        'https://apidocs.bitrix24.com/api-reference/crm/contacts/company/crm-contact-company-items-set.html',
        'Set a set of companies associated with the specified contact crm.contact.company.items.set'
    )]
    public function setItems(int $contactId, array $companyConnections): UpdatedItemResult
    {
        $items = [];
        foreach ($companyConnections as $item) {
            if (!$item instanceof CompanyConnection) {
                throw new InvalidArgumentException(
                    sprintf('array item «%s» must be «%s» type', gettype($item), CompanyConnection::class)
                );
            }

            $items[] = [
                'COMPANY_ID' => $item->companyId,
                'SORT' => $item->sort,
                'IS_PRIMARY' => $item->isPrimary ? 'Y' : 'N'
            ];
        }
        if ($items === []) {
            throw new InvalidArgumentException('empty company connections array');
        }

        return new UpdatedItemResult(
            $this->core->call('crm.contact.company.items.set', [
                'id' => $contactId,
                'items' => $items
            ])
        );
    }

    /**
     * Get a Set of Companies Associated with the Specified Contact
     *
     * @param non-negative-int $contactId
     * @link https://apidocs.bitrix24.com/api-reference/crm/contacts/company/crm-contact-company-items-get.html
     */
    #[ApiEndpointMetadata(
        'crm.contact.company.items.get',
        'https://apidocs.bitrix24.com/api-reference/crm/contacts/company/crm-contact-company-items-get.html',
        'Get a Set of Companies Associated with the Specified Contact'
    )]
    public function get(int $contactId): ContactCompanyConnectionResult
    {
        return new ContactCompanyConnectionResult($this->core->call('crm.contact.company.items.get', [
            'id' => $contactId
        ]));
    }

    /**
     * Add a Company to the Specified Contact
     *
     * @param non-negative-int $contactId
     * @link https://apidocs.bitrix24.com/api-reference/crm/contacts/company/crm-contact-company-add.html
     */
    #[ApiEndpointMetadata(
        'crm.contact.company.add',
        'https://apidocs.bitrix24.com/api-reference/crm/contacts/company/crm-contact-company-add.html',
        'Add a Company to the Specified Contact'
    )]
    public function add(int $contactId, CompanyConnection $connection): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('crm.contact.company.add', [
            'id' => $contactId,
            'fields' => [
                'COMPANY_ID' => $connection->companyId,
                'SORT' => $connection->sort,
                'IS_PRIMARY' => $connection->isPrimary ? 'Y' : 'N'
            ]
        ]));
    }

    /**
     * Delete Company from Specified Contact
     *
     * @param non-negative-int $contactId
     * @param non-negative-int $companyId
     * @link https://apidocs.bitrix24.com/api-reference/crm/contacts/company/crm-contact-company-delete.html
     */
    #[ApiEndpointMetadata(
        'crm.contact.company.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/contacts/company/crm-contact-company-delete.html',
        'Delete Company from Specified Contact'
    )]
    public function delete(int $contactId, int $companyId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('crm.contact.company.delete', [
            'id' => $contactId,
            'fields' => [
                'COMPANY_ID' => $companyId
            ]
        ]));
    }

    /**
     * Clear the set of companies associated with the specified contact
     *
     * @param non-negative-int $contactId
     * @link https://apidocs.bitrix24.com/api-reference/crm/contacts/company/crm-contact-company-items-delete.html
     */
    #[ApiEndpointMetadata(
        'crm.contact.company.items.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/contacts/company/crm-contact-company-items-delete.html',
        'Clear the set of companies associated with the specified contact'
    )]
    public function deleteItems(int $contactId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('crm.contact.company.items.delete', [
            'id' => $contactId
        ]));
    }
}
