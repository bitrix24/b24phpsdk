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

namespace Bitrix24\SDK\Services\CRM\Currency\Localization\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\CRM\Currency\Result\CurrencyItemResult;
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
     * Batch list method for currencies
     *
     * @param array{
     *   CURRENCY?: string,
     *   BASE?: string,
     *   AMOUNT_CNT?: int,
     *   AMOUNT?: double,
     *   SORT?: int,
     *   } $order
     *
     * @return Generator<int, CurrencyItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.currency.list',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-list.html',
        'Batch list method for currencies'
    )]
    public function list(array $order): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'order'  => $order,
            ]
        );
        foreach ($this->batch->getTraversableList('crm.currency.list', $order) as $key => $value) {
            yield $key => new CurrencyItemResult($value);
        }
    }

    /**
     * Batch adding currencies
     *
     * @param array <int, array{
     *   CURRENCY?: string,
     *   BASE?: string,
     *   AMOUNT_CNT?: int,
     *   AMOUNT?: double,
     *   SORT?: int,
     *   LANG?: array,
     *   }> $currencies
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.currency.add',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-add.html',
        'Batch adding currencies'
    )]
    public function add(array $currencies): Generator
    {
        $items = [];
        foreach ($currencies as $currency) {
            $items[] = [
                'fields' => $currency,
            ];
        }
        foreach ($this->batch->addEntityItems('crm.currency.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch delete currencies
     *
     * @param string[] $currencyId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.currency.delete',
        'https://training.bitrix24.com/rest_help/crm/leads/crm_lead_delete.php',
        'Batch delete currencies'
    )]
    public function delete(array $currencyId): Generator
    {
        foreach ($this->batch->deleteEntityItems('crm.currency.delete', $currencyId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
