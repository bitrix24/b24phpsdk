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

namespace Bitrix24\SDK\Services\Catalog\Ratio\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class RatiosResult extends AbstractResult
{
    /**
     * @return RatioItemResult[]
     * @throws BaseException
     */
    public function getRatios(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['ratios'] as $item) {
            $items[] = new RatioItemResult($item);
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
