<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\DeliveryExtraService\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * List of delivery extra services result for sale.delivery.extra.service.get
 */
class DeliveryExtraServicesResult extends AbstractResult
{
    /**
     * @return DeliveryExtraServiceItemResult[]
     * @throws BaseException
     */
    public function getDeliveryExtraServices(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new DeliveryExtraServiceItemResult($item);
        }

        return $items;
    }
}
