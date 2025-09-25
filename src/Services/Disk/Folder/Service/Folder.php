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

namespace Bitrix24\SDK\Services\Disk\Folder\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\Disk\Folder\Result\FolderAddedResult;
use Bitrix24\SDK\Services\Disk\Folder\Result\FolderChildrenResult;
use Bitrix24\SDK\Services\Disk\Folder\Result\FolderOperationResult;
use Bitrix24\SDK\Services\Disk\Folder\Result\FolderResult;
use Bitrix24\SDK\Services\Disk\Folder\Result\ExternalLinkResult;
use Bitrix24\SDK\Services\Disk\Folder\Result\UploadedFileResult;
use Psr\Log\LoggerInterface;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;

/**
 * Class Folder
 *
 * @package Bitrix24\SDK\Services\Disk\Folder\Service
 */
#[ApiServiceMetadata(new Scope(['disk']))]
class Folder extends \Bitrix24\SDK\Services\AbstractService
{
    /**
     * Get folder fields description
     */
    #[ApiEndpointMetadata(
        'disk.folder.getfields',
        'https://apidocs.bitrix24.com/api-reference/disk/folder/disk-folder-get-fields.html',
        'Method returns the description of folder fields.'
    )]
    public function getFields(): FieldsResult
    {
        return new FieldsResult(
            $this->core->call('disk.folder.getfields')
        );
    }

    /**
     * Get folder by ID
     */
    #[ApiEndpointMetadata(
        'disk.folder.get',
        'https://apidocs.bitrix24.com/api-reference/disk/folder/disk-folder-get.html',
        'Method returns a folder by its ID.'
    )]
    public function get(int $id): FolderResult
    {
        return new FolderResult(
            $this->core->call('disk.folder.get', [
                'id' => $id
            ])
        );
    }

    /**
     * Get list of files and folders in the folder
     */
    #[ApiEndpointMetadata(
        'disk.folder.getchildren',
        'https://apidocs.bitrix24.com/api-reference/disk/folder/disk-folder-get-children.html',
        'Method returns a list of files and folders that are directly in the folder.'
    )]
    public function getChildren(int $id, array $filter = [], int $start = 0): FolderChildrenResult
    {
        $params = ['id' => $id];

        if ($filter !== []) {
            $params['filter'] = $filter;
        }

        if ($start > 0) {
            $params['START'] = $start;
        }

        return new FolderChildrenResult(
            $this->core->call('disk.folder.getchildren', $params)
        );
    }

    /**
     * Create a subfolder
     */
    #[ApiEndpointMetadata(
        'disk.folder.addsubfolder',
        'https://apidocs.bitrix24.com/api-reference/disk/folder/disk-folder-add-subfolder.html',
        'Method creates a subfolder.'
    )]
    public function addSubfolder(int $id, array $data): FolderAddedResult
    {
        return new FolderAddedResult(
            $this->core->call('disk.folder.addsubfolder', [
                'id' => $id,
                'data' => $data
            ])
        );
    }

    /**
     * Copy folder to specified folder
     */
    #[ApiEndpointMetadata(
        'disk.folder.copyto',
        'https://apidocs.bitrix24.com/api-reference/disk/folder/disk-folder-copy-to.html',
        'Method copies a folder to the specified folder.'
    )]
    public function copyTo(int $id, int $targetFolderId): FolderOperationResult
    {
        return new FolderOperationResult(
            $this->core->call('disk.folder.copyto', [
                'id' => $id,
                'targetFolderId' => $targetFolderId
            ])
        );
    }

    /**
     * Move folder to specified folder
     */
    #[ApiEndpointMetadata(
        'disk.folder.moveto',
        'https://apidocs.bitrix24.com/api-reference/disk/folder/disk-folder-move-to.html',
        'Method moves a folder to the specified folder.'
    )]
    public function moveTo(int $id, int $targetFolderId): FolderOperationResult
    {
        return new FolderOperationResult(
            $this->core->call('disk.folder.moveto', [
                'id' => $id,
                'targetFolderId' => $targetFolderId
            ])
        );
    }

    /**
     * Rename folder
     */
    #[ApiEndpointMetadata(
        'disk.folder.rename',
        'https://apidocs.bitrix24.com/api-reference/disk/folder/disk-folder-rename.html',
        'Method renames a folder.'
    )]
    public function rename(int $id, string $newName): FolderOperationResult
    {
        return new FolderOperationResult(
            $this->core->call('disk.folder.rename', [
                'id' => $id,
                'newName' => $newName
            ])
        );
    }

    /**
     * Move folder to trash
     */
    #[ApiEndpointMetadata(
        'disk.folder.markdeleted',
        'https://apidocs.bitrix24.com/api-reference/disk/folder/disk-folder-mark-deleted.html',
        'Method moves a folder to the trash.'
    )]
    public function markDeleted(int $id): FolderOperationResult
    {
        return new FolderOperationResult(
            $this->core->call('disk.folder.markdeleted', [
                'id' => $id
            ])
        );
    }

    /**
     * Restore folder from trash
     */
    #[ApiEndpointMetadata(
        'disk.folder.restore',
        'https://apidocs.bitrix24.com/api-reference/disk/folder/disk-folder-restore.html',
        'Method restores a folder from the trash.'
    )]
    public function restore(int $id): FolderOperationResult
    {
        return new FolderOperationResult(
            $this->core->call('disk.folder.restore', [
                'id' => $id
            ])
        );
    }

    /**
     * Permanently delete folder and all its subitems
     */
    #[ApiEndpointMetadata(
        'disk.folder.deletetree',
        'https://apidocs.bitrix24.com/api-reference/disk/folder/disk-folder-delete-tree.html',
        'Method permanently deletes a folder and all its subitems.'
    )]
    public function deleteTree(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('disk.folder.deletetree', [
                'id' => $id
            ])
        );
    }

    /**
     * Get public link for folder
     */
    #[ApiEndpointMetadata(
        'disk.folder.getExternalLink',
        'https://apidocs.bitrix24.com/api-reference/disk/folder/disk-folder-get-external-link.html',
        'Method returns a public link by folder ID.'
    )]
    public function getExternalLink(int $id): ExternalLinkResult
    {
        return new ExternalLinkResult(
            $this->core->call('disk.folder.getExternalLink', [
                'id' => $id
            ])
        );
    }

    /**
     * Upload file to specified folder
     */
    #[ApiEndpointMetadata(
        'disk.folder.uploadfile',
        'https://apidocs.bitrix24.com/api-reference/disk/folder/disk-folder-upload-file.html',
        'Method uploads a new file to the specified folder.'
    )]
    public function uploadFile(int $id, array $data, $fileContent = null, bool $generateUniqueName = false, array $rights = []): UploadedFileResult
    {
        $params = [
            'id' => $id,
            'data' => $data
        ];

        if ($fileContent !== null) {
            $params['fileContent'] = $fileContent;
        }

        if ($generateUniqueName) {
            $params['generateUniqueName'] = $generateUniqueName;
        }

        if ($rights !== []) {
            $params['rights'] = $rights;
        }

        return new UploadedFileResult(
            $this->core->call('disk.folder.uploadfile', $params)
        );
    }
}
