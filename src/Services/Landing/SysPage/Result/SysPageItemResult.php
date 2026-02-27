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

namespace Bitrix24\SDK\Services\Landing\SysPage\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * SysPage item result. Represents a system page with its type and configuration.
 * Based on the documentation, each system page item contains:
 *
 * @property-read non-negative-int $id System page ID
 * @property-read string $type Type of system page (mainpage, catalog, personal, cart, order, payment, compare)
 * @property-read non-negative-int $siteId Site ID where this system page is configured
 * @property-read non-negative-int $pageId Landing page ID that serves as this system page type
 * @property-read string $active Whether the system page is active (Y/N)
 * @property-read string $url URL of the system page
 * @property-read string $title Title of the system page
 */
class SysPageItemResult extends AbstractItem
{
}
