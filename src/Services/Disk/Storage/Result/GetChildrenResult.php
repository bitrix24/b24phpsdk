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
use Bitrix24\SDK\Services\Disk\File\Result\FileItemResult;
use Bitrix24\SDK\Services\Disk\Folder\Result\FolderItemResult;

class GetChildrenResult extends AbstractResult
{
    /**
     * @return FileItemResult[]|FolderItemResult[]
     */
    public function items(): array
    {
        $items = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!is_array($result)) {
            return $items;
        }

        foreach ($result as $itemData) {
            if (!is_array($itemData)) {
                continue;
            }
            if (!isset($itemData['TYPE'])) {
                continue;
            }
            if ($itemData['TYPE'] === 'file') {
                $items[] = new FileItemResult($itemData);
            } elseif ($itemData['TYPE'] === 'folder') {
                $items[] = new FolderItemResult($itemData);
            }
        }

        return $items;
    }

    /**
     * @return FileItemResult[]
     */
    public function files(): array
    {
        return array_filter($this->items(), fn ($item): bool => $item instanceof FileItemResult);
    }

    /**
     * @return FolderItemResult[]
     */
    public function folders(): array
    {
        return array_filter($this->items(), fn ($item): bool => $item instanceof FolderItemResult);
    }
}
