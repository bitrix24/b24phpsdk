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

namespace Bitrix24\SDK\Services\Entity\Item\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int $ID
 * @property-read CarbonImmutable $TIMESTAMP_X
 * @property-read int $MODIFIED_BY
 * @property-read int $CREATED_BY
 * @property-read int $SORT
 * @property-read int|null $SECTION
 * @property-read bool $ACTIVE
 * @property-read CarbonImmutable $DATE_CREATE
 * @property-read CarbonImmutable|null $DATE_ACTIVE_FROM
 * @property-read CarbonImmutable|null $DATE_ACTIVE_TO
 * @property-read string $NAME
 * @property-read array|null $PREVIEW_PICTURE
 * @property-read string|null $PREVIEW_TEXT
 * @property-read string|null $DETAIL_TEXT
 * @property-read string|null $CODE
 * @property-read string $ENTITY
 * @property-read array|null $DETAIL_PICTURE
 */
class ItemItemResult extends AbstractCrmItem
{
}