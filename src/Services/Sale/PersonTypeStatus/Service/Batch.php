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

namespace Bitrix24\SDK\Services\Sale\PersonTypeStatus\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Result\PersonTypeStatusAddBatchResult;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Result\PersonTypeStatusItemResult;
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
     * Batch list method for person type statuses (business value person domains)
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/business-value-person-domain/sale-business-value-person-domain-list.html
     *
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param array $select Fields to select
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, PersonTypeStatusItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.businessValuePersonDomain.list',
        'https://apidocs.bitrix24.com/api-reference/sale/business-value-person-domain/sale-business-value-person-domain-list.html',
        'Batch list method for person type statuses'
    )]
    public function list(array $filter = [], array $order = [], array $select = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'filter' => $filter,
                'order' => $order,
                'select' => $select,
                'limit' => $limit,
            ]
        );

        foreach ($this->batch->getTraversableListWithCount('sale.businessValuePersonDomain.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new PersonTypeStatusItemResult($value);
        }
    }

    /**
     * Batch adding person type statuses (business value person domains)
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/business-value-person-domain/sale-business-value-person-domain-add.html
     *
     * @param array<int, array{personTypeId: int, domain: string}> $personTypeStatuses
     *
     * @return Generator<int, PersonTypeStatusAddBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.businessValuePersonDomain.add',
        'https://apidocs.bitrix24.com/api-reference/sale/business-value-person-domain/sale-business-value-person-domain-add.html',
        'Batch adding person type statuses'
    )]
    public function add(array $personTypeStatuses): Generator
    {
        $items = [];
        foreach ($personTypeStatuses as $personTypeStatus) {
            $items[] = ['fields' => $personTypeStatus];
        }

        foreach ($this->batch->addEntityItems('sale.businessValuePersonDomain.add', $items) as $key => $item) {
            yield $key => new PersonTypeStatusAddBatchResult($item);
        }
    }
}
