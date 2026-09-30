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

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * A file or folder returned by Disk search. File-only and folder-only fields are nullable.
 *
 * @property-read int $ID
 * @property-read string $NAME
 * @property-read string|null $CODE
 * @property-read int $STORAGE_ID
 * @property-read string $TYPE Object kind: file or folder
 * @property-read int|null $REAL_OBJECT_ID Folder target identifier
 * @property-read int|null $PARENT_ID
 * @property-read int $DELETED_TYPE
 * @property-read int|null $GLOBAL_CONTENT_VERSION File content version
 * @property-read int|null $FILE_ID
 * @property-read int|null $SIZE File size in bytes
 * @property-read CarbonImmutable $CREATE_TIME
 * @property-read CarbonImmutable $UPDATE_TIME
 * @property-read CarbonImmutable|null $DELETE_TIME
 * @property-read int $CREATED_BY
 * @property-read int $UPDATED_BY
 * @property-read int $DELETED_BY
 * @property-read string|null $DOWNLOAD_URL Present for files only
 * @property-read string|null $DETAIL_URL
 */
class FileSearchItemResult extends AbstractAnnotatedItem
{
}
