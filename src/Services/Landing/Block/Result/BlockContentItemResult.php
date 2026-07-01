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
 * @property-read string $sections
 * @property-read string $active
 * @property-read string $access
 * @property-read string $anchor
 * @property-read string $php
 * @property-read string $designed
 * @property-read string $repoId
 * @property-read string $content
 * @property-read string $content_ext
 * @property-read array $css
 * @property-read array $js
 * @property-read array $assetStrings
 * @property-read array $lang
 * @property-read array $manifest
 * @property-read array $dynamicParams
 */
class BlockContentItemResult extends AbstractItem
{
}
