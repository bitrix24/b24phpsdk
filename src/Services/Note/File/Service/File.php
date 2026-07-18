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

namespace Bitrix24\SDK\Services\Note\File\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Note\File\Result\FileFieldResult;
use Bitrix24\SDK\Services\Note\File\Result\FileFieldsResult;
use Bitrix24\SDK\Services\Note\File\Result\FileResult;

#[ApiServiceMetadata(new Scope(['note']))]
class File extends AbstractService
{
    /**
     * Uploads a file attachment to a document.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/file/note-file-add.html
     *
     * @param string $fileContent Base64-encoded binary file content
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.file.add',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/file/note-file-add.html',
        'Uploads a file to a document.',
        ApiVersion::v3
    )]
    public function add(int $documentId, string $fileName, string $fileContent): FileResult
    {
        return new FileResult(
            $this->core->call(
                'note.file.add',
                [
                    'documentId'  => $documentId,
                    'fileName'    => $fileName,
                    'fileContent' => $fileContent,
                ],
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns the metadata of a single `note.file` field.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/file/note-file-field-get.html
     *
     * @param array<int,string> $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.file.field.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/file/note-file-field-get.html',
        'Returns metadata of a single file field.',
        ApiVersion::v3
    )]
    public function fieldGet(string $name, array $select = []): FileFieldResult
    {
        return new FileFieldResult(
            $this->core->call(
                'note.file.field.get',
                array_filter(
                    [
                        'name'   => $name,
                        'select' => $select,
                    ],
                    static fn (mixed $value): bool => $value !== []
                ),
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns the metadata of all `note.file` fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/file/note-file-field-list.html
     *
     * @param array<int,string> $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.file.field.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/file/note-file-field-list.html',
        'Returns metadata of all file fields.',
        ApiVersion::v3
    )]
    public function fieldList(array $select = []): FileFieldsResult
    {
        return new FileFieldsResult(
            $this->core->call(
                'note.file.field.list',
                array_filter(['select' => $select], static fn (mixed $value): bool => $value !== []),
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns the document file data and a Markdown block for insertion into the document.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/file/note-file-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.file.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/file/note-file-get.html',
        'Returns the document file data and a Markdown block for insertion into the document.',
        ApiVersion::v3
    )]
    public function get(int $id, int $documentId): FileResult
    {
        return new FileResult(
            $this->core->call('note.file.get', ['id' => $id, 'documentId' => $documentId], ApiVersion::v3)
        );
    }
}
