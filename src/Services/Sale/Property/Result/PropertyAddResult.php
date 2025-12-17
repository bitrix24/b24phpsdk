<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Property\Result;

use Bitrix24\SDK\Core\Contracts\AddedItemIdResultInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result for sale.property.add
 */
class PropertyAddResult extends AbstractResult implements AddedItemIdResultInterface
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['property']['id'];
    }

    /**
     * @throws BaseException
     */
    public function getProperty(): PropertyItemResult
    {
        return new PropertyItemResult($this->getCoreResponse()->getResponseData()->getResult()['property']);
    }
}
