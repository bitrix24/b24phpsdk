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

namespace Bitrix24\SDK\Services\IM\Disk\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\Disk\Result\FileCommitResult;
use Bitrix24\SDK\Services\IM\Disk\Result\FileDeleteResult;
use Bitrix24\SDK\Services\IM\Disk\Result\FileSaveResult;
use Bitrix24\SDK\Services\IM\Disk\Result\FolderIdResult;
use Bitrix24\SDK\Services\IM\Disk\Result\RecordShareResult;

#[ApiServiceMetadata(new Scope(['im']))]
class Disk extends AbstractService
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.disk.folder.get',
        'https://apidocs.bitrix24.com/api-reference/chats/files/im-disk-folder-get.html',
        'Get the identifier of the folder where chat files are stored'
    )]
    public function getFolderId(?int $chatId = null, ?string $dialogId = null): FolderIdResult
    {
        $params = [];

        if ($chatId !== null) {
            $params['CHAT_ID'] = $chatId;
        }

        if ($dialogId !== null) {
            $params['DIALOG_ID'] = $dialogId;
        }

        return new FolderIdResult($this->core->call('im.disk.folder.get', $params));
    }

    /**
     * @param int|list<int>|null $fileId
     * @param int|list<int>|null $uploadId
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.disk.file.commit',
        'https://apidocs.bitrix24.com/api-reference/chats/files/im-disk-file-commit.html',
        'Add one or more Disk files to a chat'
    )]
    public function commitFile(
        ?int $chatId = null,
        ?string $dialogId = null,
        int|array|null $fileId = null,
        int|array|null $uploadId = null,
        ?string $message = null,
        ?bool $silentMode = null,
        ?bool $asFile = null,
    ): FileCommitResult {
        return new FileCommitResult($this->core->call('im.disk.file.commit', $this->filterNullValues([
            'CHAT_ID' => $chatId,
            'DIALOG_ID' => $dialogId,
            'FILE_ID' => $fileId,
            'UPLOAD_ID' => $uploadId,
            'MESSAGE' => $message,
            'SILENT_MODE' => $silentMode === null ? null : ($silentMode ? 'Y' : 'N'),
            'AS_FILE' => $asFile === null ? null : ($asFile ? 'Y' : 'N'),
        ])));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.disk.file.delete',
        'https://apidocs.bitrix24.com/api-reference/chats/files/im-disk-file-delete.html',
        'Delete a file from a chat folder'
    )]
    public function deleteFile(int $chatId, int $fileId): FileDeleteResult
    {
        return new FileDeleteResult($this->core->call('im.disk.file.delete', [
            'CHAT_ID' => $chatId,
            'FILE_ID' => $fileId,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.disk.file.save',
        'https://apidocs.bitrix24.com/api-reference/chats/files/im-disk-file-save.html',
        'Save a chat file to the current user personal Disk'
    )]
    public function saveFile(int $fileId): FileSaveResult
    {
        return new FileSaveResult($this->core->call('im.disk.file.save', [
            'FILE_ID' => $fileId,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.disk.record.share',
        '',
        'Share an IM record file to a dialog'
    )]
    public function shareRecord(string $dialogId, int $diskId): RecordShareResult
    {
        return new RecordShareResult($this->core->call('im.disk.record.share', [
            'DIALOG_ID' => $dialogId,
            'DISK_ID' => $diskId,
        ]));
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function filterNullValues(array $payload): array
    {
        return array_filter(
            $payload,
            static fn(mixed $value): bool => $value !== null
        );
    }
}
