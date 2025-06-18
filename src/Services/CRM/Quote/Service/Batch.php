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
use Bitrix24\SDK\Services\CRM\Quote\Result\QuoteItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['crm']))]
class Batch
{
    protected BatchOperationsInterface $batch;
    protected LoggerInterface $log;

    /**
     * Batch constructor.
     *
     * @param BatchOperationsInterface $batch
     * @param LoggerInterface          $log
     */
    public function __construct(BatchOperationsInterface $batch, LoggerInterface $log)
    {
        $this->batch = $batch;
        $this->log = $log;
    }

    /**
     * Batch list method for quotes
     *
     * @param array{
     *                         ID?: string,
     *                         TITLE?: string,
     *                         TYPE_ID?: string,
     *                         CATEGORY_ID?: string,
     *                         STAGE_ID?: string,
     *                         STAGE_SEMANTIC_ID?: string,
     *                         IS_NEW?: string,
     *                         IS_RECURRING?: string,
     *                         IS_RETURN_CUSTOMER?: string,
     *                         IS_REPEATED_APPROACH?: string,
     *                         PROBABILITY?: string,
     *                         CURRENCY_ID?: string,
     *                         OPPORTUNITY?: string,
     *                         IS_MANUAL_OPPORTUNITY?: string,
     *                         TAX_VALUE?: string,
     *                         COMPANY_ID?: string,
     *                         CONTACT_ID?: string,
     *                         CONTACT_IDS?: string,
     *                         QUOTE_ID?: string,
     *                         BEGINDATE?: string,
     *                         CLOSEDATE?: string,
     *                         OPENED?: string,
     *                         CLOSED?: string,
     *                         COMMENTS?: string,
     *                         ASSIGNED_BY_ID?: string,
     *                         CREATED_BY_ID?: string,
     *                         MODIFY_BY_ID?: string,
     *                         DATE_CREATE?: string,
     *                         DATE_MODIFY?: string,
     *                         SOURCE_ID?: string,
     *                         SOURCE_DESCRIPTION?: string,
     *                         LEAD_ID?: string,
     *                         ADDITIONAL_INFO?: string,
     *                         LOCATION_ID?: string,
     *                         ORIGINATOR_ID?: string,
     *                         ORIGIN_ID?: string,
     *                         UTM_SOURCE?: string,
     *                         UTM_MEDIUM?: string,
     *                         UTM_CAMPAIGN?: string,
     *                         UTM_CONTENT?: string,
     *                         UTM_TERM?: string,
     *                         } $order
     *
     * @param array{
     *                         ID?: int,
     *                         TITLE?: string,
     *                         TYPE_ID?: string,
     *                         CATEGORY_ID?: string,
     *                         STAGE_ID?: string,
     *                         STAGE_SEMANTIC_ID?: string,
     *                         IS_NEW?: string,
     *                         IS_RECURRING?: string,
     *                         IS_RETURN_CUSTOMER?: string,
     *                         IS_REPEATED_APPROACH?: string,
     *                         PROBABILITY?: int,
     *                         CURRENCY_ID?: string,
     *                         OPPORTUNITY?: string,
     *                         IS_MANUAL_OPPORTUNITY?: string,
     *                         TAX_VALUE?: string,
     *                         COMPANY_ID?: string,
     *                         CONTACT_ID?: string,
     *                         CONTACT_IDS?: string,
     *                         QUOTE_ID?: string,
     *                         BEGINDATE?: string,
     *                         CLOSEDATE?: string,
     *                         OPENED?: string,
     *                         CLOSED?: string,
     *                         COMMENTS?: string,
     *                         ASSIGNED_BY_ID?: string,
     *                         CREATED_BY_ID?: string,
     *                         MODIFY_BY_ID?: string,
     *                         DATE_CREATE?: string,
     *                         DATE_MODIFY?: string,
     *                         SOURCE_ID?: string,
     *                         SOURCE_DESCRIPTION?: string,
     *                         LEAD_ID?: string,
     *                         ADDITIONAL_INFO?: string,
     *                         LOCATION_ID?: string,
     *                         ORIGINATOR_ID?: string,
     *                         ORIGIN_ID?: string,
     *                         UTM_SOURCE?: string,
     *                         UTM_MEDIUM?: string,
     *                         UTM_CAMPAIGN?: string,
     *                         UTM_CONTENT?: string,
     *                         UTM_TERM?: string,
     *                         } $filter
     * @param array    $select = ['ID','TITLE','TYPE_ID','CATEGORY_ID','STAGE_ID','STAGE_SEMANTIC_ID','IS_NEW','IS_RECURRING','IS_RETURN_CUSTOMER','IS_REPEATED_APPROACH','PROBABILITY','CURRENCY_ID','OPPORTUNITY','IS_MANUAL_OPPORTUNITY','TAX_VALUE','COMPANY_ID','CONTACT_ID','CONTACT_IDS','QUOTE_ID','BEGINDATE','CLOSEDATE','OPENED','CLOSED','COMMENTS','ASSIGNED_BY_ID','CREATED_BY_ID','MODIFY_BY_ID','DATE_CREATE','DATE_MODIFY','SOURCE_ID','SOURCE_DESCRIPTION','LEAD_ID','ADDITIONAL_INFO','LOCATION_ID','ORIGINATOR_ID','ORIGIN_ID','UTM_SOURCE','UTM_MEDIUM','UTM_CAMPAIGN','UTM_CONTENT','UTM_TERM']
     * @param int|null $limit
     *
     * @return Generator<int, QuoteItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.quote.list',
        'https://training.bitrix24.com/rest_help/crm/quotes/crm_quote_list.php',
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
     *   TITLE?: string,
     *   TYPE_ID?: string,
     *   CATEGORY_ID?: string,
     *   STAGE_ID?: string,
     *   STAGE_SEMANTIC_ID?: string,
     *   IS_NEW?: string,
     *   IS_RECURRING?: string,
     *   IS_RETURN_CUSTOMER?: string,
     *   IS_REPEATED_APPROACH?: string,
     *   PROBABILITY?: int,
     *   CURRENCY_ID?: string,
     *   OPPORTUNITY?: string,
     *   IS_MANUAL_OPPORTUNITY?: string,
     *   TAX_VALUE?: string,
     *   COMPANY_ID?: string,
     *   CONTACT_ID?: string,
     *   CONTACT_IDS?: string,
     *   QUOTE_ID?: string,
     *   BEGINDATE?: string,
     *   CLOSEDATE?: string,
     *   OPENED?: string,
     *   CLOSED?: string,
     *   COMMENTS?: string,
     *   ASSIGNED_BY_ID?: string,
     *   CREATED_BY_ID?: string,
     *   MODIFY_BY_ID?: string,
     *   DATE_CREATE?: string,
     *   DATE_MODIFY?: string,
     *   SOURCE_ID?: string,
     *   SOURCE_DESCRIPTION?: string,
     *   LEAD_ID?: string,
     *   ADDITIONAL_INFO?: string,
     *   LOCATION_ID?: string,
     *   ORIGINATOR_ID?: string,
     *   ORIGIN_ID?: string,
     *   UTM_SOURCE?: string,
     *   UTM_MEDIUM?: string,
     *   UTM_CAMPAIGN?: string,
     *   UTM_CONTENT?: string,
     *   UTM_TERM?: string,
     *   }> $quotes
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.quote.add',
        'https://training.bitrix24.com/rest_help/crm/quotes/crm_quote_add.php',
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
     * Batch delete quotes
     *
     * @param int[] $quoteId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.quote.delete',
        'https://training.bitrix24.com/rest_help/crm/quotes/crm_quote_delete.php',
        'Batch delete quotes'
    )]
    public function delete(array $quoteId): Generator
    {
        foreach ($this->batch->deleteEntityItems('crm.quote.delete', $quoteId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
