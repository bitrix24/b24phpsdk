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
 * Class FieldsCatalogProductResult
 *
 * Represents the result of getting available fields for a basket item (product from catalog)
 * Contains the minimum necessary list of fields for operation with catalog products
 *
 * @property-read array|null $result
 */
class FieldsCatalogProductResult extends FieldsResult
{
    /**
     * Get fields description from response
     */
    public function getFieldsDescription(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['basketItem'];
    }
}
