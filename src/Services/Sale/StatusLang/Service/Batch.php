<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\StatusLang\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Sale\StatusLang\Result\StatusLangAddBatchResult;
use Bitrix24\SDK\Services\Sale\StatusLang\Result\StatusLangItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['sale']))]
class Batch
{
    /**
     * Batch constructor
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for status localizations
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status-lang/sale-status-lang-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, StatusLangItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.statusLang.list',
        'https://apidocs.bitrix24.com/api-reference/sale/status-lang/sale-status-lang-list.html',
        'Batch list method for status localizations'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'limit' => $limit,
            ]
        );

        foreach ($this->batch->getTraversableListWithCount('sale.statusLang.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new StatusLangItemResult($value);
        }
    }

    /**
     * Batch adding status localizations
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status-lang/sale-status-lang-add.html
     *
     * @param array<int, array{statusId: string, lid: string, name: string, description?: string}> $statusLangs
     *
     * @return Generator<int, StatusLangAddBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.statusLang.add',
        'https://apidocs.bitrix24.com/api-reference/sale/status-lang/sale-status-lang-add.html',
        'Batch adding status localizations'
    )]
    public function add(array $statusLangs): Generator
    {
        $items = array_map(
            static fn (array $statusLang): array => ['fields' => $statusLang],
            $statusLangs
        );

        foreach ($this->batch->addEntityItems('sale.statusLang.add', $items) as $key => $item) {
            yield $key => new StatusLangAddBatchResult($item);
        }
    }
}
