<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\PropertyVariant\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Single property variant wrapper result for sale.propertyvariant.get
 */
class PropertyVariantResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getPropertyVariant(): PropertyVariantItemResult
    {
        return new PropertyVariantItemResult($this->getCoreResponse()->getResponseData()->getResult()['propertyVariant']);
    }
}
