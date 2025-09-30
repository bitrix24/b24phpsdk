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

namespace Bitrix24\SDK\Services\Sale\PaymentItemBasket\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\FieldsResult;

/**
 * Class PaymentItemBasketFieldsResult
 * Represents the result of a payment item basket binding fields operation.
 */
class PaymentItemBasketFieldsResult extends FieldsResult
{
    /**
     * @throws BaseException
     */
    public function getFieldsDescription(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return $result['paymentItemBasket'] ?? [];
    }
}
