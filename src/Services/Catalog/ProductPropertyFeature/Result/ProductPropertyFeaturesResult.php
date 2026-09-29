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

namespace Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ProductPropertyFeaturesResult extends AbstractResult
{
    /**
     * @return ProductPropertyFeatureItemResult[]
     * @throws BaseException
     */
    public function productPropertyFeatures(): array
    {
        $items = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        foreach (($result['productPropertyFeatures'] ?? []) as $item) {
            $items[] = new ProductPropertyFeatureItemResult($item);
        }

        return $items;
    }
}
