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

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\CRM\Quote\Result\QuoteItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['crm']))]
class Batch
{
    /**
     * Batch constructor.
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for quotes
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
     *   } $order
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
     * @param array    $select = ['ID','ASSIGNED_BY_ID','BEGINDATA','CLIENT_ADDR','CLOSED','CLOSEDATA','COMMENTS','COMPANY_ID','CONTACT_ID','CONTACT_IDS','CONTENT','CREATED_BY_ID','CURRENCY_ID','DATE_CREATE','DATE_MODIFY','DEAL_ID','LEAD_ID','LOCATION_ID','MODIFY_BY_ID','MYCOMPANY_ID','OPENED','OPPORTUNITY','PERSON_TYPE_ID','QUOTE_NUMBER','STATUS_ID','TAX_VALUE','TERMS','TITLE','UTM_CAMPAIGN','UTM_CONTENT','UTM_MEDIUM','UTM_SOURCE','UTM_TERM']
     *
     * @return Generator<int, QuoteItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.quote.list',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-list.html',
        'Batch list method for quotes'
    )]
    public function list(array $order, array $filter, array $select, ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'order'  => $order,
                'filter' => $filter,
                'select' => $select,
                'limit'  => $limit,
            ]
        );
        foreach ($this->batch->getTraversableList('crm.quote.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new QuoteItemResult($value);
        }
    }

    /**
     * Batch adding quotes
     *
     * @param array <int, array{
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
     *   }> $quotes
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.quote.add',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-add.html',
        'Batch adding quotes'
    )]
    public function add(array $quotes): Generator
    {
        $items = [];
        foreach ($quotes as $quote) {
            $items[] = [
                'fields' => $quote,
            ];
        }

        foreach ($this->batch->addEntityItems('crm.quote.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update quotes
     *
     * Update elements in array with structure
     * element_id => [  // quote id
     *  'fields' => [] // quote fields to update
     * ]
     *
     * @param array<int, array> $entityItems
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.quote.update',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-update.html',
        'Update in batch mode a list of quotes'
    )]
    public function update(array $entityItems): Generator
    {
        foreach ($this->batch->updateEntityItems('crm.quote.update', $entityItems) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete quotes
     *
     * @param int[] $quoteId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.quote.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-delete.html',
        'Batch delete quotes'
    )]
    public function delete(array $quoteId): Generator
    {
        foreach ($this->batch->deleteEntityItems('crm.quote.delete', $quoteId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
