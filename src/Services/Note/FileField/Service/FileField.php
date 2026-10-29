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

namespace Bitrix24\SDK\Services\Note\FileField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Note\File\Result\FileFieldResult;
use Bitrix24\SDK\Services\Note\File\Result\FileFieldsResult;

#[ApiServiceMetadata(new Scope(['note']))]
class FileField extends AbstractService
{
    /**
     * @param array<int, string> $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.file.field.get',
        'https://apidocs.bitrix24.com/api-reference/note/file/note-file-field-get.html',
        'Returns metadata of a single file field.',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): FileFieldResult
    {
        return new FileFieldResult(
            $this->core->call(
                'note.file.field.get',
                array_filter(['name' => $name, 'select' => $select], static fn (mixed $value): bool => $value !== []),
                ApiVersion::v3
            )
        );
    }

    /**
     * @param array<int, string> $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.file.field.list',
        'https://apidocs.bitrix24.com/api-reference/note/file/note-file-field-list.html',
        'Returns metadata of all file fields.',
        ApiVersion::v3
    )]
    public function list(array $select = []): FileFieldsResult
    {
        return new FileFieldsResult(
            $this->core->call(
                'note.file.field.list',
                array_filter(['select' => $select], static fn (mixed $value): bool => $value !== []),
                ApiVersion::v3
            )
        );
    }
}
