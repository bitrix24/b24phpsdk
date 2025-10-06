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

namespace Bitrix24\SDK\Services\Disk\Storage\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int $ID Storage identifier
 * @property-read string $NAME Storage name
 * @property-read string|null $CODE Symbolic code
 * @property-read string $MODULE_ID Module identifier (always "disk")
 * @property-read string $ENTITY_TYPE Entity type (user, common, group, restapp)
 * @property-read string $ENTITY_ID Entity identifier
 * @property-read int $ROOT_OBJECT_ID Root folder identifier
 */
class StorageItemResult extends AbstractItem
{
}
