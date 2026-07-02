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

namespace Bitrix24\SDK\Services\IMBot\File\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\EmptyResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IMBot\File\Result\FileUploadResult;

#[ApiServiceMetadata(new Scope(['imbot']))]
class File extends AbstractService
{
    /**
     * Upload a file to a chat on behalf of the bot.
     *
     * Combines disk upload, chat attachment, and message sending in a single call.
     *
     * @param string $name File name with extension.
     * @param string $content Base64-encoded file content (max 100 MB).
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/files/file-upload.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.File.upload',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/files/file-upload.html',
        'Upload a file to a chat on behalf of the bot'
    )]
    public function upload(
        int $botId,
        string $dialogId,
        string $name,
        string $content,
        ?string $message = null,
        ?string $botToken = null,
    ): FileUploadResult {
        $fields = [
            'name' => $name,
            'content' => $content,
        ];

        if ($message !== null) {
            $fields['message'] = $message;
        }

        $params = [
            'botId' => $botId,
            'dialogId' => $dialogId,
            'fields' => $fields,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new FileUploadResult($this->core->call('imbot.v2.File.upload', $params));
    }

    /**
     * Get a download URL for a file in a chat.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/files/file-download.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imbot.v2.File.download',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/files/file-download.html',
        'Get a download URL for a file in a chat'
    )]
    public function download(
        int $botId,
        int $fileId,
        ?string $botToken = null,
    ): EmptyResult {
        $params = [
            'botId' => $botId,
            'fileId' => $fileId,
        ];

        if ($botToken !== null) {
            $params['botToken'] = $botToken;
        }

        return new EmptyResult($this->core->call('imbot.v2.File.download', $params));
    }
}
