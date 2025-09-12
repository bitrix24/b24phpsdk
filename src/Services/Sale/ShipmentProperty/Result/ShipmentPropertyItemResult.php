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
 * @property-read bool|null $active
 * @property-read string|null $code
 * @property-read string|null $defaultValue
 * @property-read string|null $description
 * @property-read bool|null $isAddress
 * @property-read bool|null $isEmail
 * @property-read bool|null $isFiltered
 * @property-read bool|null $isPayer
 * @property-read bool|null $isPhone
 * @property-read bool|null $isProfileName
 * @property-read bool|null $isZip
 * @property-read bool|null $multiple
 * @property-read string|null $name
 * @property-read int|null $personTypeId
 * @property-read int|null $propsGroupId
 * @property-read bool|null $required
 * @property-read array|null $settings
 * @property-read int|null $sort
 * @property-read string|null $type
 * @property-read bool|null $userProps
 * @property-read bool|null $util
 * @property-read string|null $xmlId
 */
class ShipmentPropertyItemResult extends AbstractItem
{
}
