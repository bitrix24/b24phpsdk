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

namespace Bitrix24\SDK\Services\Sale\ShipmentProperty\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class ShipmentPropertyItemResult
 *
 * @package Bitrix24\SDK\Services\Sale\ShipmentProperty\Result
 *
 * @property-read int|null $id
 * @property-read string|null $name
 * @property-read string|null $code
 * @property-read string|null $type
 * @property-read string|null $required
 * @property-read string|null $description
 * @property-read string|null $default
 * @property-read int|null $sort
 * @property-read string|null $multiple
 * @property-read array|null $settings
 */
class ShipmentPropertyItemResult extends AbstractItem
{
}
