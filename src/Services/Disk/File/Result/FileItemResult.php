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

namespace Bitrix24\SDK\Services\Disk\File\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * File item result
 *
 * @property-read int $ID File identifier
 * @property-read string $NAME File name
 * @property-read string $TYPE File type (always "file")
 * @property-read string|null $CODE Symbolic code
 * @property-read int|null $STORAGE_ID Storage identifier
 * @property-read int|null $PARENT_ID Parent folder identifier
 * @property-read CarbonImmutable|null $CREATE_TIME Creation time in ISO format
 * @property-read CarbonImmutable|null $UPDATE_TIME Modification time in ISO format
 * @property-read CarbonImmutable|null $DELETE_TIME Time moved to trash in ISO format
 * @property-read int $CREATED_BY User ID who created the file
 * @property-read int $UPDATED_BY User ID who modified the file
 * @property-read int $DELETED_BY User ID who moved the file to trash
 * @property-read int $GLOBAL_CONTENT_VERSION
 * @property-read int $FILE_ID
 * @property-read int $SIZE
 * @property-read int|null $DELETED_TYPE Deletion marker
 */
class FileItemResult extends AbstractItem
{
}
