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
use Bitrix24\SDK\Services\CRM\Quote\Result\QuoteResult;
use Bitrix24\SDK\Services\CRM\Quote\Result\QuotesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class Quote extends AbstractService
{
    /**
     * Quote constructor.
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * add new quote
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-add.html
     *
     * @param array{
     *   ID?: int,
     *   ASSIGNED_BY_ID?: int,
     *   LAST_ACTIVITY_BY?: int,
     *   LAST_ACTIVITY_TIME?: string,
     *   LAST_COMMUNICATION_TIME?: string,
     *   BEGINDATE?: string,
     *   CLOSEDATE?: string,
     *   ACTUAL_DATE?: string,
     *   CLIENT_ADDR?: string,
     *   CLIENT_CONTACT?: string,
     *   CLIENT_EMAIL?: string,
     *   CLIENT_PHONE?: string,
     *   CLIENT_TITLE?: string,
     *   CLIENT_TPA_ID?: string,
     *   CLIENT_TP_ID?: string,
     *   CLOSED?: bool,
     *   COMMENTS?: string,
     *   COMPANY_ID?: int,
     *   CONTACT_ID?: int,
     *   CONTACT_IDS?: int[],
     *   CONTENT?: string,
     *   CREATED_BY_ID?: int,
     *   CURRENCY_ID?: string,
     *   DATE_CREATE?: string,
     *   DATE_MODIFY?: string,
     *   DEAL_ID?: int,
     *   LEAD_ID?: int,
     *   LOCATION_ID?: int,
     *   MODIFY_BY_ID?: int,
     *   MYCOMPANY_ID?: int,
     *   OPENED?: bool,
     *   OPPORTUNITY?: string,
     *   PERSON_TYPE_ID?: int,
     *   QUOTE_NUMBER?: string,
     *   STATUS_ID?: string,
     *   TAX_VALUE?: string,
     *   TERMS?: string,
     *   TITLE?: string,
     *   UTM_CAMPAIGN?: string,
     *   UTM_CONTENT?: string,
     *   UTM_MEDIUM?: string,
     *   UTM_SOURCE?: string,
     *   UTM_TERM?: string,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.quote.add',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-add.html',
        'Method adds new quote'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.quote.add',
                [
                    'fields' => $fields
                ]
            )
        );
    }

    /**
     * Deletes the specified quote and all the associated objects.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.quote.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-delete.html',
        'Deletes the specified quote and all the associated objects.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.quote.delete',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Returns the description of the quote fields, including user fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.quote.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-fields.html',
        'Returns the description of the quote fields, including user fields.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.quote.fields'));
    }

    /**
     * Returns a quote by the quote ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.quote.get',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-get.html',
        'Returns a quote by the quote ID.'
    )]
    public function get(int $id): QuoteResult
    {
        return new QuoteResult($this->core->call('crm.quote.get', ['id' => $id]));
    }

    /**
     * Get list of quote items.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-list.html
     *
     * @param array   $order     - order of quote items
     * @param array   $filter    - filter array
     * @param array   $select    = ['ID','ASSIGNED_BY_ID','BEGINDATA','CLIENT_ADDR','CLOSED','CLOSEDATA','COMMENTS','COMPANY_ID','CONTACT_ID','CONTACT_IDS','CONTENT','CREATED_BY_ID','CURRENCY_ID','DATE_CREATE','DATE_MODIFY','DEAL_ID','LEAD_ID','LOCATION_ID','MODIFY_BY_ID','MYCOMPANY_ID','OPENED','OPPORTUNITY','PERSON_TYPE_ID','QUOTE_NUMBER','STATUS_ID','TAX_VALUE','TERMS','TITLE','UTM_CAMPAIGN','UTM_CONTENT','UTM_MEDIUM','UTM_SOURCE','UTM_TERM']
     * @param integer $startItem - entity number to start from (usually returned in 'next' field of previous 'crm.quote.list' API call)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.quote.list',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-list.html',
        'Get list of quote items.'
    )]
    public function list(array $order, array $filter, array $select, int $startItem = 0): QuotesResult
    {
        return new QuotesResult(
            $this->core->call(
                'crm.quote.list',
                [
                    'order'  => $order,
                    'filter' => $filter,
                    'select' => $select,
                    'start'  => $startItem,
                ]
            )
        );
    }

    /**
     * Updates the specified (existing) quote.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-update.html
     *
     * @param array{
     *   ID?: int,
     *   ASSIGNED_BY_ID?: int,
     *   BEGINDATA?: string,
     *   CLIENT_ADDR?: string,
     *   CLOSED?: bool,
     *   CLOSEDATA?: string,
     *   COMMENTS?: string,
     *   COMPANY_ID?: int,
     *   CONTACT_ID?: int,
     *   CONTACT_IDS?: int[],
     *   CONTENT?: string,
     *   CREATED_BY_ID?: int,
     *   CURRENCY_ID?: string,
     *   DATE_CREATE?: string,
     *   DATE_MODIFY?: string,
     *   DEAL_ID?: int,
     *   LEAD_ID?: int,
     *   LOCATION_ID?: int,
     *   MODIFY_BY_ID?: int,
     *   MYCOMPANY_ID?: int,
     *   OPENED?: bool,
     *   OPPORTUNITY?: string,
     *   PERSON_TYPE_ID?: int,
     *   QUOTE_NUMBER?: string,
     *   STATUS_ID?: string,
     *   TAX_VALUE?: string,
     *   TERMS?: string,
     *   TITLE?: string,
     *   UTM_CAMPAIGN?: string,
     *   UTM_CONTENT?: string,
     *   UTM_MEDIUM?: string,
     *   UTM_SOURCE?: string,
     *   UTM_TERM?: string,
     *   }        $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.quote.update',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-update.html',
        'Updates the specified (existing) quote.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.quote.update',
                [
                    'id'     => $id,
                    'fields' => $fields
                ]
            )
        );
    }

    /**
     * Count quotes by filter
     *
     * @param array{
     *   ID?: int,
     *   ASSIGNED_BY_ID?: int,
     *   BEGINDATA?: string,
     *   CLIENT_ADDR?: string,
     *   CLOSED?: bool,
     *   CLOSEDATA?: string,
     *   COMMENTS?: string,
     *   COMPANY_ID?: int,
     *   CONTACT_ID?: int,
     *   CONTACT_IDS?: int[],
     *   CONTENT?: string,
     *   CREATED_BY_ID?: int,
     *   CURRENCY_ID?: string,
     *   DATE_CREATE?: string,
     *   DATE_MODIFY?: string,
     *   DEAL_ID?: int,
     *   LEAD_ID?: int,
     *   LOCATION_ID?: int,
     *   MODIFY_BY_ID?: int,
     *   MYCOMPANY_ID?: int,
     *   OPENED?: bool,
     *   OPPORTUNITY?: string,
     *   PERSON_TYPE_ID?: int,
     *   QUOTE_NUMBER?: string,
     *   STATUS_ID?: string,
     *   TAX_VALUE?: string,
     *   TERMS?: string,
     *   TITLE?: string,
     *   UTM_CAMPAIGN?: string,
     *   UTM_CONTENT?: string,
     *   UTM_MEDIUM?: string,
     *   UTM_SOURCE?: string,
     *   UTM_TERM?: string,
     *   } $filter
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function countByFilter(array $filter = []): int
    {
        return $this->list([], $filter, ['ID'], 1)->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
