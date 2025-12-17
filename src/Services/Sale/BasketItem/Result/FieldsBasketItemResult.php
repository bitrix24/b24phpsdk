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

namespace Bitrix24\SDK\Services\Sale\BasketItem\Result;

use Bitrix24\SDK\Core\Result\FieldsResult;

/**
 * Class FieldsBasketItemResult
 * Represents field descriptions for basket item
 *
 * @package Bitrix24\SDK\Services\Sale\BasketItem\Result
 */
class FieldsBasketItemResult extends FieldsResult
{
    /**
     * Get field descriptions for basket item
     *
     * @return array{type:string, isRequired:bool, isReadOnly:bool, isImmutable:bool}[]
     */
    #[\Override]
    public function getFieldsDescription(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['basketItem'];
    }
}
