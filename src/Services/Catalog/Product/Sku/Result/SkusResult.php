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

namespace Bitrix24\SDK\Services\Catalog\Product\Sku\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class SkusResult extends AbstractResult
{
    /**
     * @return SkuItemResult[]
     * @throws BaseException
     */
    public function getSkus(): array
    {
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['units'] as $unit) {
            $res[] = new SkuItemResult($unit);
        }

        return $res;
    }
}
