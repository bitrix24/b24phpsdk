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

namespace Bitrix24\SDK\Services\Sale\PersonType\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class PersonTypeItemResult
 *
 * @property-read int $id
 * @property-read string $code
 * @property-read string $name
 * @property-read string $active
 * @property-read string $sort
 * @property-read string $xmlId
 */
class PersonTypeItemResult extends AbstractItem
{
}
