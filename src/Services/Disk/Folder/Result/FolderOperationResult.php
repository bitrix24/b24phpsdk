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
 * Class FolderOperationResult
 *
 * @package Bitrix24\SDK\Services\Disk\Folder\Result
 */
class FolderOperationResult extends AbstractResult
{
    /**
     * Check if operation was successful
     */
    public function isSuccess(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult()['ID'];
    }

    /**
     * Get folder item (if operation returns folder data)
     */
    public function folder(): ?FolderItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return isset($result['ID']) ? new FolderItemResult($result) : null;
    }
}
