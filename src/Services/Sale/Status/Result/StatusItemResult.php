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

namespace Bitrix24\SDK\Services\Sale\Status\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class StatusItemResult - represents a single status item
 *
 * @package Bitrix24\SDK\Services\Sale\Status\Result
 *
 * @property-read string $id Status identifier
 * @property-read string $type Status type (ORDER or DELIVERY)
 * @property-read int $sort Sort order
 * @property-read string $name Status name
 * @property-read string $description Status description
 * @property-read bool $notify Customer notification of status change
 */
class StatusItemResult extends AbstractItem
{
}
