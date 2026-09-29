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

namespace Bitrix24\SDK\Services\Landing\RepoWidget\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Landing;
use Bitrix24\SDK\Services\Landing\RepoWidget\Result\RepoWidgetItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['landing']))]
class Batch
{
    public function __construct(protected Landing\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of Vibe widgets registered by the current application
     *
     * @link https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-get-list.html
     *
     * @param array $select Fields to select (empty means all fields)
     * @param array $filter Filter conditions
     * @param int|null $limit Maximum number of widgets to return
     *
     * @return Generator<int, RepoWidgetItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.repowidget.getlist',
        'https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-get-list.html',
        'Batch list of Vibe widgets registered by the current application'
    )]
    public function list(array $select = [], array $filter = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'select' => $select,
                'filter' => $filter,
                'limit' => $limit,
            ]
        );

        $params = [
            'select' => $select,
            'filter' => $filter,
        ];

        foreach ($this->batch->getTraversableListByOffset('landing.repowidget.getlist', $params, $limit) as $key => $value) {
            yield $key => new RepoWidgetItemResult($value);
        }
    }

    /**
     * Batch registering Vibe widgets
     *
     * @link https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-register.html
     *
     * @param array<int, array{code: string, fields: array}> $widgets
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     * @throws InvalidArgumentException
     */
    #[ApiBatchMethodMetadata(
        'landing.repowidget.register',
        'https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-register.html',
        'Batch registering Vibe widgets'
    )]
    public function register(array $widgets): Generator
    {
        $items = [];
        foreach ($widgets as $cnt => $widget) {
            if (!array_key_exists('code', $widget) || !array_key_exists('fields', $widget)) {
                throw new InvalidArgumentException(
                    sprintf('array keys «code» and «fields» are required in widget at position %s', $cnt)
                );
            }

            $items[] = [
                'code' => $widget['code'],
                'fields' => $widget['fields'],
            ];
        }

        foreach ($this->batch->addEntityItems('landing.repowidget.register', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch removing Vibe widgets
     *
     * @link https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-unregister.html
     *
     * @param string[] $codes Widget codes
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.repowidget.unregister',
        'https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-unregister.html',
        'Batch removing Vibe widgets'
    )]
    public function unregister(array $codes): Generator
    {
        foreach ($this->batch->deleteEntityItems('landing.repowidget.unregister', $codes) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
