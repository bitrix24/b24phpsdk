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

namespace Bitrix24\SDK\Services\Sale\Order\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class OrderResult
 * Represents the result of a single order operation.
 */
class OrderResult extends AbstractResult
{
    /**
     * Returns the order as OrderItemResult
     */
    public function order(): OrderItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return new OrderItemResult($result['order'] ?? []);
    }
}
