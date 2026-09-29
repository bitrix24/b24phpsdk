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

namespace Bitrix24\SDK\Services\Catalog\Extra\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ExtrasResult extends AbstractResult
{
    /**
     * @return ExtraItemResult[]
     * @throws BaseException
     */
    public function getExtras(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['extras'] as $item) {
            $items[] = new ExtraItemResult($item);
        }

        return $items;
    }

    /**
     * @throws BaseException
     */
    public function getTotal(): int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getTotal() ?? 0;
    }
}
