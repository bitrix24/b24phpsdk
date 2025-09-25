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

namespace Bitrix24\SDK\Services\Disk\Folder\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class FolderItemResult
 *
 * @property-read int $ID Folder identifier
 * @property-read string $NAME Folder name
 * @property-read string $TYPE Object type (folder)
 * @property-read string|null $CODE Symbolic code
 * @property-read int $STORAGE_ID Storage identifier
 * @property-read int|null $REAL_OBJECT_ID Real object identifier
 * @property-read int|null $PARENT_ID Parent folder identifier
 * @property-read CarbonImmutable|null $CREATE_TIME Creation time
 * @property-read CarbonImmutable|null $UPDATE_TIME Modification time
 * @property-read CarbonImmutable|null $DELETE_TIME Time moved to trash
 * @property-read int $CREATED_BY User ID who created the folder
 * @property-read int $UPDATED_BY User ID who modified the folder
 * @property-read int $DELETED_BY User ID who moved the folder to trash
 * @property-read int $DELETED_TYPE Deletion marker
 *
 * @package Bitrix24\SDK\Services\Disk\Folder\Result
 */
class FolderItemResult extends AbstractItem
{
}
