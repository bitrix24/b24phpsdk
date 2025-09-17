<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Delivery\Result;

use Bitrix24\SDK\Core\Contracts\AddedItemIdResultInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result for sale.delivery.add
 */
class DeliveryAddResult extends AbstractResult implements AddedItemIdResultInterface
{
    /**
     * @throws BaseException
     */
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['parent']['ID'];
    }

    /**
     * @throws BaseException
     */
    public function getParent(): DeliveryItemResult
    {
        return new DeliveryItemResult($this->getCoreResponse()->getResponseData()->getResult()['parent']);
    }

    /**
     * @return DeliveryItemResult[]
     * @throws BaseException
     */
    public function getProfiles(): array
    {
        $items = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (isset($result['profiles']) && is_array($result['profiles'])) {
            foreach ($result['profiles'] as $item) {
                $items[] = new DeliveryItemResult($item);
            }
        }

        return $items;
    }
}
