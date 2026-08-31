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
 * Result for im.v2.File.upload.
 *
 * Response shape: { result: { file: {...}, messageId: int, chatId: int, dialogId: string } }
 */
class FileV2UploadResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function file(): FileV2ItemResult
    {
        return new FileV2ItemResult($this->getCoreResponse()->getResponseData()->getResult()['file']);
    }

    /**
     * @throws BaseException
     */
    public function getMessageId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['messageId'];
    }

    /**
     * @throws BaseException
     */
    public function getChatId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['chatId'];
    }

    /**
     * @throws BaseException
     */
    public function getDialogId(): string
    {
        return (string)$this->getCoreResponse()->getResponseData()->getResult()['dialogId'];
    }
}
