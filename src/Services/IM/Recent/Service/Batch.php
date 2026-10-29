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

namespace Bitrix24\SDK\Services\IM\Recent\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\IM;
use Bitrix24\SDK\Services\IM\Recent\Result\RecentItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['im']))]
class Batch
{
    public function __construct(protected IM\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of recent dialogs of the current user
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/recent/im-recent-list.html
     *
     * @param array{
     *   SKIP_OPENLINES?: string,
     *   SKIP_DIALOG?: string,
     *   SKIP_CHAT?: string,
     *   LAST_MESSAGE_DATE?: string,
     *   UNREAD_ONLY?: string,
     *   PARSE_TEXT?: string,
     *   GET_ORIGINAL_TEXT?: string,
     *   SKIP_UNDISTRIBUTED_OPENLINES?: string,
     *   ONLY_OPENLINES?: string,
     *   ONLY_COPILOT?: string,
     *   ONLY_CHANNEL?: string,
     *   CAN_MANAGE_MESSAGES?: string,
     * } $params selection parameters, pagination parameters OFFSET and LIMIT are set automatically
     *
     * @return Generator<int, RecentItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.recent.list',
        'https://apidocs.bitrix24.com/api-reference/chats/recent/im-recent-list.html',
        'Batch list of recent dialogs of the current user'
    )]
    public function list(array $params = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'params' => $params,
                'limit' => $limit,
            ]
        );

        foreach ($this->batch->getTraversableListByOffset('im.recent.list', $params, $limit) as $key => $value) {
            yield $key => new RecentItemResult($value);
        }
    }
}
