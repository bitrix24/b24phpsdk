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

namespace Bitrix24\SDK\Services\IMOpenLines\Config\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class OptionItemResult
 *
 * Represents a single open line
 * 
 * @property-read string $id     Connector identifier
 * @property-read string $name   Connector display name
 */
class OptionItemResult extends AbstractItem
{
}
