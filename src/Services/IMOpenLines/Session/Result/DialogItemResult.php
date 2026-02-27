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

namespace Bitrix24\SDK\Services\IMOpenLines\Session\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * @property-read string|null $avatar
 * @property-read string $color
 * @property-read CarbonImmutable $dateCreate
 * @property-read string|null $dialogId
 * @property-read string $entityData1
 * @property-read string $entityData2
 * @property-read string $entityData3
 * @property-read string $entityId
 * @property-read string $entityType
 * @property-read bool $extranet
 * @property-read int $id
 * @property-read array $managerList
 * @property-read string $messageType
 * @property-read string $name
 * @property-read int $owner
 * @property-read string $type
 */
class DialogItemResult extends AbstractItem
{
}
