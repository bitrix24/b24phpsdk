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

namespace Bitrix24\SDK\Services\IM\Dialog\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\IM;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogUserItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['im']))]
class Batch
{
    public function __construct(protected IM\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list dialog participants
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/chat-users/im-dialog-users-list.html
     *
     * @param string $dialogId chatXXX for a chat, sgXXX for a group or project chat, XXX for a private chat with a user
     * @param string|null $skipExternalExceptTypes comma-separated system user types to keep, for example "bot,email"
     *
     * @return Generator<int, DialogUserItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.dialog.users.list',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-users/im-dialog-users-list.html',
        'Batch list dialog participants'
    )]
    public function usersList(
        string $dialogId,
        bool $skipExternal = false,
        ?string $skipExternalExceptTypes = null,
        ?int $limit = null,
    ): Generator {
        $this->log->debug(
            'batchUsersList',
            [
                'dialogId' => $dialogId,
                'skipExternal' => $skipExternal,
                'skipExternalExceptTypes' => $skipExternalExceptTypes,
                'limit' => $limit,
            ]
        );

        $users = $this->batch->getTraversableListByOffset(
            'im.dialog.users.list',
            [
                'DIALOG_ID' => $dialogId,
                'SKIP_EXTERNAL' => $skipExternal ? 'Y' : 'N',
                'SKIP_EXTERNAL_EXCEPT_TYPES' => $skipExternalExceptTypes,
            ],
            $limit
        );
        foreach ($users as $key => $value) {
            yield $key => new DialogUserItemResult($value);
        }
    }
}
