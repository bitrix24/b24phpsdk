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

namespace Bitrix24\SDK\Services\Disk\Storage\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;
use Bitrix24\SDK\Services\Disk\Folder\Result\FolderItemResult;

class AddFolderResult extends AbstractResult
{
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['ID'];
    }

    public function folder(): FolderItemResult
    {
        return new FolderItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
