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

namespace Bitrix24\SDK\Services\Entity\Section\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int $ID
 * @property-read string $ENTITY
 * @property-read string $NAME
 * @property-read string|null $DESCRIPTION
 * @property-read bool $ACTIVE
 * @property-read int $SORT
 * @property-read array|null $PICTURE
 * @property-read array|null $DETAIL_PICTURE
 * @property-read int|null $SECTION
 */
class SectionItemResult extends AbstractCrmItem
{
}
