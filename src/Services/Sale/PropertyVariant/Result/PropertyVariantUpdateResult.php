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

namespace Bitrix24\SDK\Services\Sale\PropertyVariant\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;

/**
 * Result for sale.propertyvariant.update
 */
class PropertyVariantUpdateResult extends UpdatedItemResult
{
    /**
     * @throws BaseException
     */
    public function getPropertyVariant(): PropertyVariantItemResult
    {
        return new PropertyVariantItemResult($this->getCoreResponse()->getResponseData()->getResult()['propertyVariant']);
    }

    /**
     * @throws BaseException
     */
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['propertyVariant']['id'];
    }

    /**
     * Override the default isSuccess() method to check for the presence of propertyVariant in the response
     *
     * @throws BaseException
     */
    #[\Override]
    public function isSuccess(): bool
    {
        return isset($this->getCoreResponse()->getResponseData()->getResult()['propertyVariant']);
    }
}
