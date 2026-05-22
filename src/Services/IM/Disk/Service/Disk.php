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
use Bitrix24\SDK\Services\IM\Disk\Result\FolderIdResult;

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
}
