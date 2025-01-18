<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\User\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['user']))]
class Batch
{
    /**
     * Batch constructor.
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    #[ApiBatchMethodMetadata(
        'user.add',
        'https://apidocs.bitrix24.com/api-reference/user/user-add.html',
        'Invite User'
    )]
    public function add(array $users): Generator
    {
        $items = $users;
        foreach ($this->batch->addEntityItems('user.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    #[ApiBatchMethodMetadata(
        'user.get',
        'https://apidocs.bitrix24.com/api-reference/user/user-get.html',
        'Get User List by Filter'
    )]
    public function get(array $order, array $filter, bool $isAdminMode = false, ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'order' => $order,
                'filter' => $filter,
                'isAdminMode' => $isAdminMode,
                'limit' => $limit,
            ]
        );

        if ($order === []) {
            $order = ['ID' => 'ASC'];
        }

        foreach (
            $this->batch->getTraversableList(
                'user.get',
                // wrong structure of sort
                [],
                $filter,
                [],
                $limit,
                [
                    'ADMIN_MODE' => $isAdminMode ? 'Y' : 'N',
                    'sort' => array_keys($order)[0]
                ]
            ) as $key => $value
        ) {
            yield $key => new DealItemResult($value);
        }
    }
}