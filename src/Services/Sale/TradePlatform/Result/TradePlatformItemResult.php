<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\TradePlatform\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class TradePlatformItemResult
 * @package Bitrix24\SDK\Services\Sale\TradePlatform\Result
 *
 * @property-read int $id Trade platform ID
 * @property-read string $code Trade platform code
 * @property-read string|null $name Trade platform name
 * @property-read string|null $description Trade platform description
 * @property-read string|null $catalogSectionTabClassName Catalog section tab class name
 * @property-read string|null $class Class name
 */
class TradePlatformItemResult extends AbstractItem
{
}
