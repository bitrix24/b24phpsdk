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
use Bitrix24\SDK\Services\CRM\Company\Result\LeadContactConnectionResult;
use Psr\Log\LoggerInterface;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;

#[ApiServiceMetadata(new Scope(['crm']))]
class LeadContact extends AbstractService
{
    /**
     * Retrieves the description of fields for the lead-contact link used by the methods in the crm.lead.contact.* family
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/management-communication/crm-lead-contact-fields.html
     *
     * @return FieldsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.lead.contact.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/management-communication/crm-lead-contact-fields.html',
        'Retrieves the description of fields for the lead-contact link used by the methods in the crm.lead.contact.* family'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.lead.contact.fields'));
    }

    /**
     * Attaches a list of contacts to the specified lead
     *
     * @param non-negative-int $leadId
     * @param ContactConnection[] $contactConnections
     * @throws InvalidArgumentException
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/management-communication/crm-lead-contact-items-set.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.contact.items.set',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/management-communication/crm-lead-contact-items-set.html',
        'Attaches a list of contacts to the specified lead'
    )]
    public function setItems(int $leadId, array $contactConnections): UpdatedItemResult
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
            $this->core->call('crm.lead.contact.items.set', [
                'id' => $leadId,
                'items' => $items
            ])
        );
    }

    /**
     * Retrieves a list of contacts linked to the lead
     *
     * @param non-negative-int $leadId
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/management-communication/crm-lead-contact-items-get.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.contact.items.get',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/management-communication/crm-lead-contact-items-get.html',
        'Retrieves a list of contacts linked to the lead'
    )]
    public function get(int $leadId): LeadContactConnectionResult
    {
        return new LeadContactConnectionResult($this->core->call('crm.lead.contact.items.get', [
            'id' => $leadId
        ]));
    }

    /**
     * Removes a list of contacts from the lead
     *
     * @param non-negative-int $leadId
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/management-communication/crm-lead-contact-items-delete.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.contact.items.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/management-communication/crm-lead-contact-items-delete.html',
        'Removes a list of contacts from the lead'
    )]
    public function deleteItems(int $leadId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('crm.lead.contact.items.delete', [
            'id' => $leadId
        ]));
    }

    /**
     * Adds a contact link to the specified lead
     *
     * @param non-negative-int $leadId
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/management-communication/index.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.contact.add',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/management-communication/index.html',
        'Adds a contact link to the specified lead'
    )]
    public function add(int $leadId, ContactConnection $connection): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('crm.lead.contact.add', [
            'id' => $leadId,
            'fields' => [
                'CONTACT_ID' => $connection->contactId,
                'SORT' => $connection->sort,
                'IS_PRIMARY' => $connection->isPrimary ? 'Y' : 'N'
            ]
        ]));
    }

    /**
     * Removes a contact link from the specified lead
     *
     * @param non-negative-int $leadId
     * @param non-negative-int $contactId
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/management-communication/crm-lead-contact-delete.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.contact.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/management-communication/crm-lead-contact-delete.html',
        'Removes a contact link from the specified lead'
    )]
    public function delete(int $leadId, int $contactId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('crm.lead.contact.delete', [
            'id' => $leadId,
            'fields' => [
                'CONTACT_ID' => $contactId
            ]
        ]));
    }
}