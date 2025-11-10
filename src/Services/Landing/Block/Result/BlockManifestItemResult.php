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
 * @property-read array $block
 * @property-read array $cards
 * @property-read array $nodes
 * @property-read array $style
 * @property-read array $assets
 * @property-read array $groups
 * @property-read int $timestamp
 * @property-read array $attrs
 * @property-read array $callbacks
 * @property-read array $menu
 * @property-read string $namespace
 * @property-read string $code
 * @property-read string $preview
 */
class BlockManifestItemResult extends AbstractItem
{
}
