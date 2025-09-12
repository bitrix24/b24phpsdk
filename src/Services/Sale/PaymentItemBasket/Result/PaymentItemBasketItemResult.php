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

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class PaymentItemBasketItemResult
 * Represents a single payment item basket binding returned by Bitrix24 REST API.
 *
 * Fields and their types are taken from Bitrix24 API (sale.paymentitembasket.getfields).
 *
 * @property-read int|null $id Binding identifier
 * @property-read int|null $paymentId Payment identifier
 * @property-read int|null $basketId Basket item identifier
 * @property-read float|null $quantity Quantity of the product
 * @property-read string|null $xmlId External record identifier
 * @property-read CarbonImmutable|null $dateInsert Date when the binding was created
 */
class PaymentItemBasketItemResult extends AbstractItem
{
    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return isset($this->data['id']) ? (int)$this->data['id'] : null;
    }

    /**
     * @return int|null
     */
    public function getPaymentId(): ?int
    {
        return isset($this->data['paymentId']) ? (int)$this->data['paymentId'] : null;
    }

    /**
     * @return int|null
     */
    public function getBasketId(): ?int
    {
        return isset($this->data['basketId']) ? (int)$this->data['basketId'] : null;
    }

    /**
     * @return float|null
     */
    public function getQuantity(): ?float
    {
        return isset($this->data['quantity']) ? (float)$this->data['quantity'] : null;
    }

    /**
     * @return string|null
     */
    public function getXmlId(): ?string
    {
        return isset($this->data['xmlId']) ? (string)$this->data['xmlId'] : null;
    }

    /**
     * @return CarbonImmutable|null
     */
    public function getDateInsert(): ?CarbonImmutable
    {
        return isset($this->data['dateInsert']) ? CarbonImmutable::parse($this->data['dateInsert']) : null;
    }
}