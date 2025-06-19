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

namespace Bitrix24\SDK\Services\CRM\Quote\Service;

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
use Bitrix24\SDK\Services\CRM\Quote\Result\QuoteContactConnectionResult;
use Psr\Log\LoggerInterface;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;

#[ApiServiceMetadata(new Scope(['crm']))]
class QuoteContact extends AbstractService
{
    /**
     * Get Field Descriptions for Estimate-Contact Connection
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/quote/index.html
     *
     * @return FieldsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.quote.contact.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/index.html',
        'Get Field Descriptions for Estimate-Contact Connection'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.quote.contact.fields'));
    }

    /**
     * Set a set of contacts associated with the specified estimate
     *
     * @param non-negative-int $quoteId
     * @param ContactConnection[] $contactConnections
     * @throws InvalidArgumentException
     * @link https://apidocs.bitrix24.com/api-reference/crm/quote/index.html
     */
    #[ApiEndpointMetadata(
        'crm.quote.contact.items.set',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/index.html',
        'Set a set of contacts associated with the specified estimate crm.quote.contact.items.set'
    )]
    public function setItems(int $quoteId, array $contactConnections): UpdatedItemResult
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
            $this->core->call('crm.quote.contact.items.set', [
                'id' => $quoteId,
                'items' => $items
            ])
        );
    }

    /**
     * Get a set of contacts associated with the specified estimate
     *
     * @param non-negative-int $quoteId
     * @link https://apidocs.bitrix24.com/api-reference/crm/quote/index.html
     */
    #[ApiEndpointMetadata(
        'crm.quote.contact.items.get',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/index.html',
        'Get a set of contacts associated with the specified estimate crm.quote.contact.items.get'
    )]
    public function get(int $quoteId): QuoteContactConnectionResult
    {
        return new QuoteContactConnectionResult($this->core->call('crm.quote.contact.items.get', [
            'id' => $quoteId
        ]));
    }

    /**
     * Get a set of contacts associated with the specified estimates
     *
     * @param non-negative-int $quoteId
     * @link https://apidocs.bitrix24.com/api-reference/crm/quote/index.html
     */
    #[ApiEndpointMetadata(
        'crm.quote.contact.items.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/index.html',
        'Clear the set of contacts associated with the specified estimate'
    )]
    public function deleteItems(int $quoteId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('crm.quote.contact.items.delete', [
            'id' => $quoteId
        ]));
    }

    /**
     * Add Contact to the Specified Estimate
     *
     * @param non-negative-int $quoteId
     * @link https://apidocs.bitrix24.com/api-reference/crm/quote/index.html
     */
    #[ApiEndpointMetadata(
        'crm.quote.contact.add',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/index.html',
        'Add Contact to the Specified Estimate'
    )]
    public function add(int $quoteId, ContactConnection $connection): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('crm.quote.contact.add', [
            'id' => $quoteId,
            'fields' => [
                'CONTACT_ID' => $connection->contactId,
                'SORT' => $connection->sort,
                'IS_PRIMARY' => $connection->isPrimary ? 'Y' : 'N'
            ]
        ]));
    }

    /**
     * Delete Contact from Specified Estimate
     *
     * @param non-negative-int $quoteId
     * @param non-negative-int $contactId
     * @link https://apidocs.bitrix24.com/api-reference/crm/quote/index.html
     */
    #[ApiEndpointMetadata(
        'crm.quote.contact.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/index.html',
        'Delete Contact from Specified Estimate'
    )]
    public function delete(int $quoteId, int $contactId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('crm.quote.contact.delete', [
            'id' => $quoteId,
            'fields' => [
                'CONTACT_ID' => $contactId
            ]
        ]));
    }
}