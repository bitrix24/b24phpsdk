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
 * Class FolderAddedResult
 *
 * @package Bitrix24\SDK\Services\Disk\Folder\Result
 */
class FolderAddedResult extends AbstractResult
{
    /**
     * Get added folder ID
     */
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['ID'];
    }

    /**
     * Get added folder item
     */
    public function folder(): FolderItemResult
    {
        return new FolderItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
