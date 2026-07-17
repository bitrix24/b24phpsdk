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

namespace Bitrix24\SDK\Services\Catalog\ProductImage\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ProductImagesResult extends AbstractResult
{
    /**
     * @return ProductImageItemResult[]
     * @throws BaseException
     */
    public function getProductImages(): array
    {
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['productImages'] as $productImage) {
            $res[] = new ProductImageItemResult($productImage);
        }

        return $res;
    }
}
