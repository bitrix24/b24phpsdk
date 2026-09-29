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

namespace Bitrix24\SDK\Services\IM\FileV2\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result for im.v2.File.download.
 *
 * Response shape: { result: { downloadUrl: string } }
 *
 * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/im.v2/files/file-download.html
 */
class FileV2DownloadResult extends AbstractResult
{
    /**
     * Returns a one-time download URL for the requested file.
     *
     * @throws BaseException
     */
    public function getDownloadUrl(): string
    {
        return (string)$this->getCoreResponse()->getResponseData()->getResult()['downloadUrl'];
    }
}
