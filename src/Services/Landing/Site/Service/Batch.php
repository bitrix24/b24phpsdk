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

namespace Bitrix24\SDK\Services\Landing\Site\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Landing;
use Bitrix24\SDK\Services\Landing\Site\Result\SiteItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['landing']))]
class Batch
{
    public function __construct(protected Landing\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of sites
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-get-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter conditions
     * @param array $order Sort order, ID is always added as a tie-breaker
     * @param int|null $limit Maximum number of sites to return
     *
     * @return Generator<int, SiteItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.site.getList',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-get-list.html',
        'Batch list of sites'
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

        $params = [
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
        ];

        foreach ($this->batch->getTraversableListByOffset('landing.site.getList', $params, $limit) as $key => $value) {
            yield $key => new SiteItemResult($value);
        }
    }

    /**
     * Batch adding sites
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-add.html
     *
     * @param array<int, array<string, mixed>> $sites Field values for creating sites
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.site.add',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-add.html',
        'Batch adding sites'
    )]
    public function add(array $sites): Generator
    {
        $items = [];
        foreach ($sites as $site) {
            $items[] = ['fields' => $site];
        }

        foreach ($this->batch->addEntityItems('landing.site.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update sites
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-update.html
     *
     * @param array<int, array<string, mixed>> $sites Fields to update keyed by site id
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.site.update',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-update.html',
        'Batch update sites'
    )]
    public function update(array $sites): Generator
    {
        $items = [];
        foreach ($sites as $id => $site) {
            $items[$id] = ['fields' => $site];
        }

        foreach ($this->batch->updateEntityItems('landing.site.update', $items) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete sites
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-delete.html
     *
     * @param int[] $siteIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.site.delete',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-delete.html',
        'Batch delete sites'
    )]
    public function delete(array $siteIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('landing.site.delete', $siteIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
