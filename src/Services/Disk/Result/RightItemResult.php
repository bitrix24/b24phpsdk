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
 * Class RightItemResult
 *
 * @property-read int $ID        // Identifier of the access level
 * @property-read string $NAME   // Symbolic code
 * @property-read string $TITLE  // Title
 */
class RightItemResult extends AbstractItem
{
}
