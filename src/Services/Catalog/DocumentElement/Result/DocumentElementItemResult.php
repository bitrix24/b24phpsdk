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

namespace Bitrix24\SDK\Services\Catalog\DocumentElement\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Money\Money;

/**
 * @property-read int        $id
 * @property-read int        $docId
 * @property-read int        $elementId
 * @property-read int|null   $storeFrom
 * @property-read int|null   $storeTo
 * @property-read float      $amount
 * @property-read Money|null $purchasingPrice
 */
class DocumentElementItemResult extends AbstractItem
{
}
