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

namespace Bitrix24\SDK\Services\Disk\Storage\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Disk\Storage\Result\AddFolderResult;
use Bitrix24\SDK\Services\Disk\Storage\Result\GetChildrenResult;
use Bitrix24\SDK\Services\Disk\Storage\Result\StorageResult;
use Bitrix24\SDK\Services\Disk\Storage\Result\StoragesResult;
use Bitrix24\SDK\Services\Disk\Storage\Result\StorageTypesResult;
use Bitrix24\SDK\Services\Disk\Storage\Result\UploadFileResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['disk']))]
class Storage extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Returns the description of the storage fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.storage.getfields',
        'https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-get-fields.html',
        'Returns the description of storage fields.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('disk.storage.getfields'));
    }

    /**
     * Retrieves the storage by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-get.html
     *
     * @param int $id Storage identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.storage.get',
        'https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-get.html',
        'Returns the storage by its identifier.'
    )]
    public function get(int $id): StorageResult
    {
        return new StorageResult(
            $this->core->call('disk.storage.get', [
                'id' => $id
            ])
        );
    }

    /**
     * Renames the storage. Only the application storage can be renamed.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-rename.html
     *
     * @param int    $id      Storage identifier
     * @param string $newName New name
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.storage.rename',
        'https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-rename.html',
        'Renames the storage. Only the application storage can be renamed.'
    )]
    public function rename(int $id, string $newName): StorageResult
    {
        return new StorageResult(
            $this->core->call('disk.storage.rename', [
                'id' => $id,
                'newName' => $newName
            ])
        );
    }

    /**
     * Returns a list of available storages.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-get-list.html
     *
     * @param array $filter Optional filter parameters
     * @param int   $start  The ordinal number of the list item from which to return the next items
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.storage.getlist',
        'https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-get-list.html',
        'Returns a list of available storages.'
    )]
    public function list(array $filter = [], int $start = 0): StoragesResult
    {
        return new StoragesResult(
            $this->core->call('disk.storage.getlist', [
                'filter' => $filter,
                'start' => $start
            ])
        );
    }

    /**
     * Returns a list of storage types.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-get-types.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.storage.gettypes',
        'https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-get-types.html',
        'Returns a list of storage types.'
    )]
    public function getTypes(): StorageTypesResult
    {
        return new StorageTypesResult($this->core->call('disk.storage.gettypes'));
    }

    /**
     * Creates a folder in the root of the storage.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-add-folder.html
     *
     * @param int   $id   Storage identifier
     * @param array $data Array describing the folder. Required field NAME - the name of the new folder
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.storage.addfolder',
        'https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-add-folder.html',
        'Creates a folder in the root of the storage.'
    )]
    public function addFolder(int $id, array $data): AddFolderResult
    {
        return new AddFolderResult(
            $this->core->call('disk.storage.addfolder', [
                'id' => $id,
                'data' => $data
            ])
        );
    }

    /**
     * Returns a list of files and folders that are directly in the root of the storage.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-get-children.html
     *
     * @param int   $id     Storage identifier
     * @param array $filter Optional filter parameters
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.storage.getchildren',
        'https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-get-children.html',
        'Returns a list of files and folders that are directly in the root of the storage.'
    )]
    public function getChildren(int $id, array $filter = []): GetChildrenResult
    {
        return new GetChildrenResult(
            $this->core->call('disk.storage.getchildren', [
                'id' => $id,
                'filter' => $filter
            ])
        );
    }

    /**
     * Uploads a new file to the root of the storage.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-upload-file.html
     *
     * @param int      $id                 Storage identifier
     * @param string   $fileContent        Upload file in Base64 format
     * @param array    $data               Array describing the file. Required field NAME - name of the new file
     * @param bool     $generateUniqueName Optional, defaults to false. Generate unique name for the uploaded file
     * @param array    $rights             Optional, array of access permissions for the uploaded file
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.storage.uploadfile',
        'https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-upload-file.html',
        'Uploads a new file to the root of the storage.'
    )]
    public function uploadFile(
        int $id,
        string $fileContent,
        array $data,
        bool $generateUniqueName = false,
        array $rights = []
    ): UploadFileResult {
        return new UploadFileResult(
            $this->core->call('disk.storage.uploadfile', [
                'id' => $id,
                'fileContent' => $fileContent,
                'data' => $data,
                'generateUniqueName' => $generateUniqueName,
                'rights' => $rights
            ])
        );
    }

    /**
     * Returns the description of the storage that the application can work with to store its data.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-get-for-app.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.storage.getforapp',
        'https://apidocs.bitrix24.com/api-reference/disk/storage/disk-storage-get-for-app.html',
        'Returns the description of the storage that the application can work with to store its data.'
    )]
    public function getForApp(): StorageResult
    {
        return new StorageResult($this->core->call('disk.storage.getforapp'));
    }
}
