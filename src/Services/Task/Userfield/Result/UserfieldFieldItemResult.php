<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\Userfield\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read string $type
 * @property-read string $title
 * @property-read bool $isReadOnly
 * @property-read bool $isImmutable
 */
class UserfieldFieldItemResult extends AbstractItem
{
}
