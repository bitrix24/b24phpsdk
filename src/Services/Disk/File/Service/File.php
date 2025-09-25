<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Disk\File\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Disk\File\Result\FileCopiedResult;
use Bitrix24\SDK\Services\Disk\File\Result\FileDeletedResult;
use Bitrix24\SDK\Services\Disk\File\Result\FileExternalLinkResult;
use Bitrix24\SDK\Services\Disk\File\Result\FileMarkedDeletedResult;
use Bitrix24\SDK\Services\Disk\File\Result\FileMovedResult;
use Bitrix24\SDK\Services\Disk\File\Result\FileRenamedResult;
use Bitrix24\SDK\Services\Disk\File\Result\FileRestoredFromVersionResult;
use Bitrix24\SDK\Services\Disk\File\Result\FileRestoredResult;
use Bitrix24\SDK\Services\Disk\File\Result\FileResult;
use Bitrix24\SDK\Services\Disk\File\Result\FileVersionsResult;
use Bitrix24\SDK\Services\Disk\File\Result\FileVersionUploadedResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['disk']))]
class File extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Returns the description of file fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.file.getfields',
        'https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-get-fields.html',
        'Returns a description of the file fields.'
    )]
    public function getFields(): FieldsResult
    {
        return new FieldsResult(
            $this->core->call('disk.file.getfields')
        );
    }

    /**
     * Returns a file by its ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-get.html
     *
     * @param int $id File identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.file.get',
        'https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-get.html',
        'Returns a file by its ID.'
    )]
    public function get(int $id): FileResult
    {
        return new FileResult(
            $this->core->call('disk.file.get', [
                'id' => $id
            ])
        );
    }

    /**
     * Renames a file.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-rename.html
     *
     * @param int    $id      File identifier
     * @param string $newName New file name
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.file.rename',
        'https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-rename.html',
        'Renames a file.'
    )]
    public function rename(int $id, string $newName): FileRenamedResult
    {
        return new FileRenamedResult(
            $this->core->call('disk.file.rename', [
                'id' => $id,
                'newName' => $newName
            ])
        );
    }

    /**
     * Copies a file to the specified folder.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-copy-to.html
     *
     * @param int $id             File identifier
     * @param int $targetFolderId Identifier of the folder to which the copy is made
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.file.copyto',
        'https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-copy-to.html',
        'Copies a file to the specified folder.'
    )]
    public function copyTo(int $id, int $targetFolderId): FileCopiedResult
    {
        return new FileCopiedResult(
            $this->core->call('disk.file.copyto', [
                'id' => $id,
                'targetFolderId' => $targetFolderId
            ])
        );
    }

    /**
     * Moves a file to the specified folder.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-move-to.html
     *
     * @param int $id             File identifier
     * @param int $targetFolderId Target folder identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.file.moveto',
        'https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-move-to.html',
        'Moves a file to the specified folder.'
    )]
    public function moveTo(int $id, int $targetFolderId): FileMovedResult
    {
        return new FileMovedResult(
            $this->core->call('disk.file.moveto', [
                'id' => $id,
                'targetFolderId' => $targetFolderId
            ])
        );
    }

    /**
     * Permanently deletes a file.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-delete.html
     *
     * @param int $id File identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.file.delete',
        'https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-delete.html',
        'Permanently deletes a file.'
    )]
    public function delete(int $id): FileDeletedResult
    {
        return new FileDeletedResult(
            $this->core->call('disk.file.delete', [
                'id' => $id
            ])
        );
    }

    /**
     * Moves a file to the trash.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-mark-deleted.html
     *
     * @param int $id File identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.file.markdeleted',
        'https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-mark-deleted.html',
        'Moves a file to the trash.'
    )]
    public function markDeleted(int $id): FileMarkedDeletedResult
    {
        return new FileMarkedDeletedResult(
            $this->core->call('disk.file.markdeleted', [
                'id' => $id
            ])
        );
    }

    /**
     * Restores a file from the trash.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-restore.html
     *
     * @param int $id File identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.file.restore',
        'https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-restore.html',
        'Restores a file from the trash.'
    )]
    public function restore(int $id): FileRestoredResult
    {
        return new FileRestoredResult(
            $this->core->call('disk.file.restore', [
                'id' => $id
            ])
        );
    }

    /**
     * Uploads a new version of a file.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-upload-version.html
     *
     * @param int    $id          File identifier
     * @param string $fileContent Upload the file in Base64 format
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.file.uploadversion',
        'https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-upload-version.html',
        'Uploads a new version of a file.'
    )]
    public function uploadVersion(int $id, string $fileContent): FileVersionUploadedResult
    {
        return new FileVersionUploadedResult(
            $this->core->call('disk.file.uploadversion', [
                'id' => $id,
                'fileContent' => $fileContent
            ])
        );
    }

    /**
     * Returns a list of file versions sorted in descending order by creation date.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-get-versions.html
     *
     * @param int        $id     File identifier
     * @param array|null $filter Optional parameter. Supports filtering by fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.file.getVersions',
        'https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-get-versions.html',
        'Returns a list of file versions sorted in descending order by creation date.'
    )]
    public function getVersions(int $id, ?array $filter = null): FileVersionsResult
    {
        $params = ['id' => $id];
        if ($filter !== null) {
            $params['filter'] = $filter;
        }

        return new FileVersionsResult(
            $this->core->call('disk.file.getVersions', $params)
        );
    }

    /**
     * Restores a file from a specific version.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-restore-from-version.html
     *
     * @param int $id        File identifier
     * @param int $versionId Version identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.file.restoreFromVersion',
        'https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-restore-from-version.html',
        'Restores a file from a specific version.'
    )]
    public function restoreFromVersion(int $id, int $versionId): FileRestoredFromVersionResult
    {
        return new FileRestoredFromVersionResult(
            $this->core->call('disk.file.restoreFromVersion', [
                'id' => $id,
                'versionId' => $versionId
            ])
        );
    }

    /**
     * Returns a public link by file identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-get-external-link.html
     *
     * @param int $id File identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.file.getExternalLink',
        'https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-get-external-link.html',
        'Returns a public link by file identifier.'
    )]
    public function getExternalLink(int $id): FileExternalLinkResult
    {
        return new FileExternalLinkResult(
            $this->core->call('disk.file.getExternalLink', [
                'id' => $id
            ])
        );
    }
}
