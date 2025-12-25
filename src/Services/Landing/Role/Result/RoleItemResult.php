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

namespace Bitrix24\SDK\Services\Landing\Role\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int $ID Role identifier
 * @property-read string $TITLE Role title/name
 * @property-read string $XML_ID Role XML identifier
 */
class RoleItemResult extends AbstractItem
{
}
