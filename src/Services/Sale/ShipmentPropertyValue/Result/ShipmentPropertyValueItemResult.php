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

namespace Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class ShipmentPropertyValueItemResult
 *
 * @property-read int         $id
 * @property-read string|null $code
 * @property-read string      $name
 * @property-read int|null    $shipmentId
 * @property-read int         $shipmentPropsId
 * @property-read string|null $shipmentPropsXmlId
 * @property-read string      $value
 */
class ShipmentPropertyValueItemResult extends AbstractItem
{
}
