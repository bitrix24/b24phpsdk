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
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Common\ContactConnection;
use Bitrix24\SDK\Services\CRM\Company\Result\CompanyContactConnectionResult;
use Psr\Log\LoggerInterface;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;

#[ApiServiceMetadata(new Scope(['crm']))]
class CompanyContact extends AbstractService
{
    /**
     * Get Field Descriptions for Company-Contact Connection
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/companies/contacts/crm-company-contact-fields.html
     *
     * @return FieldsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.company.contact.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/contacts/crm-company-contact-fields.html',
        'Get Field Descriptions for Company-Contact Connection'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.company.contact.fields'));
    }

    /**
     * Set a set of contacts associated with the specified company
     *
     * @param non-negative-int $companyId
     * @param ContactConnection[] $contactConnections
     * @throws InvalidArgumentException
     * @link https://apidocs.bitrix24.com/api-reference/crm/companies/contacts/crm-company-contact-items-set.html
     */
    #[ApiEndpointMetadata(
        'crm.company.contact.items.set',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/contacts/crm-company-contact-items-set.html',
        'Set a set of contacts associated with the specified company crm.company.contact.items.set'
    )]
    public function setItems(int $companyId, array $contactConnections): UpdatedItemResult
    {
        $items = [];
        foreach ($contactConnections as $item) {
            if (!$item instanceof ContactConnection) {
                throw new InvalidArgumentException(
                    sprintf('array item «%s» must be «%s» type', gettype($item), ContactConnection::class)
                );
            }

            $items[] = [
                'CONTACT_ID' => $item->contactId,
                'SORT' => $item->sort,
                'IS_PRIMARY' => $item->isPrimary ? 'Y' : 'N'
            ];
        }
        if ($items === []) {
            throw new InvalidArgumentException('empty contact connections array');
        }

        return new UpdatedItemResult(
            $this->core->call('crm.company.contact.items.set', [
                'id' => $companyId,
                'items' => $items
            ])
        );
    }

    /**
     * Get a set of contacts associated with the specified company
     *
     * @param non-negative-int $companyId
     * @link https://apidocs.bitrix24.com/api-reference/crm/companies/contacts/crm-company-contact-items-get.html
     */
    #[ApiEndpointMetadata(
        'crm.company.contact.items.get',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/contacts/crm-company-contact-items-get.html',
        'Get a set of contacts associated with the specified company crm.company.contact.items.get'
    )]
    public function get(int $companyId): CompanyContactConnectionResult
    {
        return new CompanyContactConnectionResult($this->core->call('crm.company.contact.items.get', [
            'id' => $companyId
        ]));
    }

    /**
     * Get a set of contacts associated with the specified company
     *
     * @param non-negative-int $companyId
     * @link https://apidocs.bitrix24.com/api-reference/crm/companies/contacts/crm-company-contact-items-delete.html
     */
    #[ApiEndpointMetadata(
        'crm.company.contact.items.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/contacts/crm-company-contact-items-delete.html',
        'Clear the set of contacts associated with the specified company'
    )]
    public function deleteItems(int $companyId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('crm.company.contact.items.delete', [
            'id' => $companyId
        ]));
    }

    /**
     * Add Contact to the Specified Company
     *
     * @param non-negative-int $companyId
     * @link https://apidocs.bitrix24.com/api-reference/crm/companies/contacts/crm-company-contact-add.html
     */
    #[ApiEndpointMetadata(
        'crm.company.contact.add',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/contacts/crm-company-contact-add.html',
        'Add Contact to the Specified Company'
    )]
    public function add(int $companyId, ContactConnection $connection): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('crm.company.contact.add', [
            'id' => $companyId,
            'fields' => [
                'CONTACT_ID' => $connection->contactId,
                'SORT' => $connection->sort,
                'IS_PRIMARY' => $connection->isPrimary ? 'Y' : 'N'
            ]
        ]));
    }

    /**
     * Delete Contact from Specified Company
     *
     * @param non-negative-int $companyId
     * @param non-negative-int $contactId
     * @link https://apidocs.bitrix24.com/api-reference/crm/companies/contacts/crm-company-contact-delete.html
     */
    #[ApiEndpointMetadata(
        'crm.company.contact.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/contacts/crm-company-contact-delete.html',
        'Delete Contact from Specified Company'
    )]
    public function delete(int $companyId, int $contactId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('crm.company.contact.delete', [
            'id' => $companyId,
            'fields' => [
                'CONTACT_ID' => $contactId
            ]
        ]));
    }
}