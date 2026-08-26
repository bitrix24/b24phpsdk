<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\StoreProduct\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int        $id
 * @property-read int        $productId
 * @property-read int        $storeId
 * @property-read float      $amount
 * @property-read float|null $quantityReserved
 */
class StoreProductItemResult extends AbstractItem
{
}
