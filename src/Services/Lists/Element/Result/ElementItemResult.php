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

namespace Bitrix24\SDK\Services\Lists\Element\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class ElementItemResult
 *
 * @property-read string $ID
 * @property-read string|null $CODE
 * @property-read string $NAME
 * @property-read string|null $IBLOCK_SECTION_ID
 * @property-read string $CREATED_BY
 * @property-read CarbonImmutable $DATE_CREATE
 * @property-read string $SORT
 */
class ElementItemResult extends AbstractItem
{
}
