<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Property\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Single property wrapper result for sale.property.get
 */
class PropertyResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getProperty(): PropertyItemResult
    {
        return new PropertyItemResult($this->getCoreResponse()->getResponseData()->getResult()['property']);
    }
}
