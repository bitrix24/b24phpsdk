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

namespace Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ProductPropertySectionsResult extends AbstractResult
{
    /**
     * @return ProductPropertySectionItemResult[]
     * @throws BaseException
     */
    public function getProductPropertySections(): array
    {
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['productPropertySections'] as $item) {
            $res[] = new ProductPropertySectionItemResult($item);
        }

        return $res;
    }
}
