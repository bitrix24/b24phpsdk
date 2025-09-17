<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Delivery\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result for sale.delivery.config.get
 */
class DeliveryConfigGetResult extends AbstractResult
{
    /**
     * @return array<array{CODE: string, VALUE: mixed}>
     * @throws BaseException
     */
    public function getConfig(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult();
    }
}
