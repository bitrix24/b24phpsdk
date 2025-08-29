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

namespace Bitrix24\SDK\Services\Sale\PropertyGroup\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class PropertyGroupItemResult
 *
 * @property-read int $id
 * @property-read string $name
 * @property-read int $personTypeId
 * @property-read int $sort
 */
class PropertyGroupItemResult extends AbstractItem
{
}
