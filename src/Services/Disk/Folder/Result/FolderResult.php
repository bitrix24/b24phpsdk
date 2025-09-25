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
 * Class FolderResult
 *
 * @package Bitrix24\SDK\Services\Disk\Folder\Result
 */
class FolderResult extends AbstractResult
{
    /**
     * Get folder item
     */
    public function folder(): FolderItemResult
    {
        return new FolderItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}