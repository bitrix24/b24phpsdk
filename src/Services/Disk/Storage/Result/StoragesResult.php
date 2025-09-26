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

class StoragesResult extends AbstractResult
{
    /**
     * @return StorageItemResult[]
     */
    public function storages(): array
    {
        $items = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!is_array($result)) {
            return $items;
        }

        foreach ($result as $itemData) {
            if (is_array($itemData)) {
                $items[] = new StorageItemResult($itemData);
            }
        }

        return $items;
    }
}
