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

namespace Bitrix24\SDK\Services\IM\Chat\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Services\IM;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['im']))]
class Batch
{
    public function __construct(protected IM\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch create chats
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/im-chat-add.html
     *
     * @param array<int, array{
     *   USERS: int[],
     *   TYPE?: string,
     *   TITLE?: string,
     *   DESCRIPTION?: string,
     *   COLOR?: string,
     *   MESSAGE?: string,
     *   AVATAR?: string,
     *   ENTITY_TYPE?: string,
     *   ENTITY_ID?: string,
     *   COPILOT_MAIN_ROLE?: string,
     * }> $chats
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.chat.add',
        'https://apidocs.bitrix24.com/api-reference/chats/im-chat-add.html',
        'Batch create chats'
    )]
    public function add(array $chats): Generator
    {
        foreach ($this->batch->addEntityItems('im.chat.add', $chats) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }
}
