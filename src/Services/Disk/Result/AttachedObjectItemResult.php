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

namespace Bitrix24\SDK\Services\Disk\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class AttachedObjectItemResult
 *
 * @property-read int $ID              // Attachment binding identifier
 * @property-read int $OBJECT_ID       // File identifier from Drive
 * @property-read string $MODULE_ID    // Module that owns the user property
 * @property-read string $ENTITY_TYPE  // Entity type
 * @property-read int $ENTITY_ID       // Identifier of the entity to which the attachment is made
 * @property-read string $CREATE_TIME  // Creation time in ISO format
 * @property-read int $CREATED_BY      // Identifier of the user who created the binding
 * @property-read string $DOWNLOAD_URL // Download URL for the file
 * @property-read string $NAME         // File name
 * @property-read int $SIZE            // File size in bytes
 */
class AttachedObjectItemResult extends AbstractItem
{
}
