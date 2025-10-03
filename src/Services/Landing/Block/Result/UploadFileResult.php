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

namespace Bitrix24\SDK\Services\Landing\Block\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class UploadFileResult extends AbstractResult
{
    /**
     * @return array Contains file ID and URL
     * @throws BaseException
     */
    public function getUploadFileData(): array
    {
        echo "\n UploadFileResult \n";
        print_r($this->getCoreResponse()->getResponseData()->getResult());
        echo "\n";

        return $this->getCoreResponse()->getResponseData()->getResult();
    }

    /**
     * @return int File ID
     * @throws BaseException
     */
    public function getId(): int
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return (int)$result['id'];
    }

    /**
     * @return string File URL
     * @throws BaseException
     */
    public function getUrl(): string
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return $result['src'];
    }

    /**
     * @deprecated Use getId() instead
     * @return int|null File ID
     * @throws BaseException
     */
    public function getFileId(): ?int
    {
        return $this->getId();
    }

    /**
     * @deprecated Use getUrl() instead
     * @return string Direct path to uploaded file
     * @throws BaseException
     */
    public function getFilePath(): string
    {
        return $this->getUrl();
    }
}
