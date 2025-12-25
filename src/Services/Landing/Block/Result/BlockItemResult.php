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

namespace Bitrix24\SDK\Services\Landing\Block\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read non-negative-int $id
 * @property-read non-negative-int $lid
 * @property-read string $code
 * @property-read string $name
 * @property-read string $active
 * @property-read array $meta
 */
class BlockItemResult extends AbstractItem
{
}
