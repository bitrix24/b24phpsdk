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

namespace Bitrix24\SDK\Services\IM\Disk\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
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
     * Batch delete files from chat folders
     *
     * The file is deleted only if it was sent by the current user.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/files/im-disk-file-delete.html
     *
     * @param array<int, array{
     *   CHAT_ID: int,
     *   FILE_ID: int,
     * }> $files
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.disk.file.delete',
        'https://apidocs.bitrix24.com/api-reference/chats/files/im-disk-file-delete.html',
        'Batch delete files from chat folders'
    )]
    public function deleteFile(array $files): Generator
    {
        foreach ($this->batch->processEntityItems('im.disk.file.delete', $files) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
