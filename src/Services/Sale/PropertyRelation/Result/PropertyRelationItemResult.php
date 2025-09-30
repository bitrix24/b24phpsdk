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

namespace Bitrix24\SDK\Services\Sale\PropertyRelation\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class PropertyRelationItemResult
 * Represents a single property relation returned by Bitrix24 REST API.
 *
 * Fields and their types are taken from Bitrix24 API (sale.propertyRelation.getFields).
 *
 * @property-read int|null $entityId Entity identifier
 * @property-read string|null $entityType Entity type: P — payment system, D — delivery, L — landing, T — trading platform
 * @property-read int|null $propertyId Property identifier
 */
class PropertyRelationItemResult extends AbstractItem
{
}
