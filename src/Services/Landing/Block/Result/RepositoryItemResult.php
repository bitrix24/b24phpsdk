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
 * @property-read string $name
 * @property-read array $meta
 * @property-read string $new
 * @property-read array $type
 * @property-read string $specialType
 * @property-read string $separator
 * @property-read string $app_code
 * @property-read array $items
 */
class RepositoryItemResult extends AbstractItem
{
}
