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

namespace Bitrix24\SDK\Services\Landing\Repo\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Landing;
use Bitrix24\SDK\Services\Landing\Repo\Result\RepoItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['landing']))]
class Batch
{
    public function __construct(protected Landing\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of blocks from the current application repository
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/user-blocks/landing-repo-get-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter conditions
     * @param array $order Sort order, ID is always added as a tie-breaker
     * @param int|null $limit Maximum number of blocks to return
     *
     * @return Generator<int, RepoItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.repo.getList',
        'https://apidocs.bitrix24.com/api-reference/landing/user-blocks/landing-repo-get-list.html',
        'Batch list of blocks from the current application repository'
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

        foreach ($this->batch->getTraversableListByOffset('landing.repo.getList', $params, $limit) as $key => $value) {
            yield $key => new RepoItemResult($value);
        }
    }

    /**
     * Batch adding blocks to the repository
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/user-blocks/landing-repo-register.html
     *
     * @param array<int, array{code: string, fields: array, manifest?: array}> $blocks
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     * @throws InvalidArgumentException
     */
    #[ApiBatchMethodMetadata(
        'landing.repo.register',
        'https://apidocs.bitrix24.com/api-reference/landing/user-blocks/landing-repo-register.html',
        'Batch adding blocks to the repository'
    )]
    public function register(array $blocks): Generator
    {
        $items = [];
        foreach ($blocks as $cnt => $block) {
            if (!array_key_exists('code', $block) || !array_key_exists('fields', $block)) {
                throw new InvalidArgumentException(
                    sprintf('array keys «code» and «fields» are required in block at position %s', $cnt)
                );
            }

            $item = [
                'code' => $block['code'],
                'fields' => $block['fields'],
            ];
            if (array_key_exists('manifest', $block)) {
                $item['manifest'] = $block['manifest'];
            }

            $items[] = $item;
        }

        foreach ($this->batch->addEntityItems('landing.repo.register', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch removing blocks from the repository
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/user-blocks/landing-repo-unregister.html
     *
     * @param string[] $codes Block codes
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.repo.unregister',
        'https://apidocs.bitrix24.com/api-reference/landing/user-blocks/landing-repo-unregister.html',
        'Batch removing blocks from the repository'
    )]
    public function unregister(array $codes): Generator
    {
        foreach ($this->batch->deleteEntityItems('landing.repo.unregister', $codes) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
