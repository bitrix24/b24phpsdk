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

namespace Bitrix24\SDK\Services\IM\FileV2\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\FileV2\Result\FileV2DownloadResult;
use Bitrix24\SDK\Services\IM\FileV2\Result\FileV2UploadResult;

/**
 * IM v2 file service.
 *
 * @see https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/im.v2/files/
 */
#[ApiServiceMetadata(new Scope(['im']))]
class FileV2 extends AbstractService
{
    /**
     * Upload a file to a chat.
     *
     * @param string $name File name with extension.
     * @param string $content Base64-encoded file content.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/im.v2/files/file-upload.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.v2.File.upload',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/im.v2/files/file-upload.html',
        'Upload a file to a chat'
    )]
    public function upload(
        string $dialogId,
        string $name,
        string $content,
        ?string $message = null,
    ): FileV2UploadResult {
        $fields = [
            'name' => $name,
            'content' => $content,
        ];

        if ($message !== null) {
            $fields['message'] = $message;
        }

        return new FileV2UploadResult($this->core->call('im.v2.File.upload', [
            'dialogId' => $dialogId,
            'fields' => $fields,
        ]));
    }

    /**
     * Get a download URL for a file in a chat.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/im.v2/files/file-download.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.v2.File.download',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/im.v2/files/file-download.html',
        'Get a download URL for a file in a chat'
    )]
    public function download(int $fileId): FileV2DownloadResult
    {
        return new FileV2DownloadResult($this->core->call('im.v2.File.download', ['fileId' => $fileId]));
    }
}
