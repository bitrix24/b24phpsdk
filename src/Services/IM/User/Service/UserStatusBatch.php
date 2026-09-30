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

namespace Bitrix24\SDK\Services\IM\User\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\IM;
use Bitrix24\SDK\Services\IM\User\UserStatusType;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['im']))]
class UserStatusBatch
{
    public function __construct(protected IM\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch set statuses of the current user
     *
     * Commands are executed sequentially, the last status in the list becomes the current one.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/users/im-user-status-set.html
     *
     * @param UserStatusType[] $userStatusTypes
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.user.status.set',
        'https://apidocs.bitrix24.com/api-reference/chats/users/im-user-status-set.html',
        'Batch set statuses of the current user'
    )]
    public function set(array $userStatusTypes): Generator
    {
        $commandsParameters = array_map(
            static fn (UserStatusType $userStatusType): array => ['STATUS' => $userStatusType->value],
            array_values($userStatusTypes)
        );

        foreach ($this->batch->processEntityItems('im.user.status.set', $commandsParameters) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }
}
