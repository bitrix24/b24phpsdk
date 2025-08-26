<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\PropertyVariant\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class PropertyVariantItemResult
 *
 * @property-read int $id
 * @property-read int $orderPropsId
 * @property-read string $name
 * @property-read string $value
 * @property-read string $xmlId
 * @property-read int $sort
 * @property-read string $description
 */
class PropertyVariantItemResult extends AbstractItem
{
}
