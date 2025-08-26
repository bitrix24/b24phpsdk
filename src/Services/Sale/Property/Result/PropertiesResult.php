<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Property\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * List of properties result for sale.property.list
 */
class PropertiesResult extends AbstractResult
{
    /**
     * @return PropertyItemResult[]
     * @throws BaseException
     */
    public function getProperties(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['properties'] as $item) {
            $items[] = new PropertyItemResult($item);
        }

        return $items;
    }

    /**
     * @throws BaseException
     */
    public function getTotal(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
