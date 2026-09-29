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

namespace Bitrix24\SDK\Services\Landing\Page\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Landing;
use Bitrix24\SDK\Services\Landing\Page\Result\PageItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['landing']))]
class Batch
{
    public function __construct(protected Landing\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of pages
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-get-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter conditions
     * @param array $order Sort order, ID is always added as a tie-breaker
     * @param int|null $limit Maximum number of pages to return
     * @param bool $getPreview Return page previews
     * @param bool $getUrls Return public addresses of pages
     * @param bool $checkArea Return flag IS_AREA indicating whether the page is an included area
     *
     * @return Generator<int, PageItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.landing.getList',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-get-list.html',
        'Batch list of pages'
    )]
    public function list(
        array $select = [],
        array $filter = [],
        array $order = [],
        ?int $limit = null,
        bool $getPreview = false,
        bool $getUrls = false,
        bool $checkArea = false
    ): Generator {
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

        if ($getPreview) {
            $params['get_preview'] = 1;
        }

        if ($getUrls) {
            $params['get_urls'] = 1;
        }

        if ($checkArea) {
            $params['check_area'] = 1;
        }

        foreach ($this->batch->getTraversableListByOffset('landing.landing.getList', $params, $limit) as $key => $value) {
            yield $key => new PageItemResult($value);
        }
    }

    /**
     * Batch adding pages
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-add.html
     *
     * @param array<int, array<string, mixed>> $pages Field values for creating pages
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.landing.add',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-add.html',
        'Batch adding pages'
    )]
    public function add(array $pages): Generator
    {
        $items = [];
        foreach ($pages as $page) {
            $items[] = ['fields' => $page];
        }

        foreach ($this->batch->addEntityItems('landing.landing.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update pages
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-update.html
     *
     * @param array<int, array<string, mixed>> $pages Fields to update keyed by page id
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.landing.update',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-update.html',
        'Batch update pages'
    )]
    public function update(array $pages): Generator
    {
        $items = [];
        foreach ($pages as $lid => $page) {
            $items[$lid] = ['fields' => $page];
        }

        foreach ($this->batch->updateEntityItems('landing.landing.update', $items) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete pages
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-delete.html
     *
     * @param int[] $pageIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.landing.delete',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-delete.html',
        'Batch delete pages'
    )]
    public function delete(array $pageIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('landing.landing.delete', $pageIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
