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

use Bitrix24\SDK\Core\Result\AbstractResult;

class ProductPropertySectionResult extends AbstractResult
{
    public function productPropertySection(): ProductPropertySectionItemResult
    {
        return new ProductPropertySectionItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()['productPropertySection']
        );
    }
}
