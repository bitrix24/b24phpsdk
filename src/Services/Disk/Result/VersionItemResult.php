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
 * Class VersionItemResult
 *
 * @property-read int $ID                        // Version identifier
 * @property-read int $CREATED_BY                // Identifier of the user who created the version
 * @property-read string $CREATE_TIME           // Creation time in ISO format
 * @property-read string $DOWNLOAD_URL          // Link to download the content
 * @property-read int $GLOBAL_CONTENT_VERSION   // Incremental version counter relative to the file
 * @property-read string $NAME                  // File name at the time of version creation
 * @property-read int $OBJECT_ID                // Identifier of the file to which the version belongs
 * @property-read int $SIZE                     // Size of the version in bytes
 */
class VersionItemResult extends AbstractItem
{
}
