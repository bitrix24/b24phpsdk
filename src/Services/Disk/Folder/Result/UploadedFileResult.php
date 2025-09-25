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

namespace Bitrix24\SDK\Services\Disk\Folder\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class UploadedFileResult
 *
 * @package Bitrix24\SDK\Services\Disk\Folder\Result
 */
class UploadedFileResult extends AbstractResult
{
    /**
     * Get uploaded file ID
     */
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['ID'];
    }

    /**
     * Get uploaded file data
     */
    public function getFile(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult();
    }
}