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

namespace Bitrix24\SDK\Services\CRM\Requisites\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int|null $ID
 * @property-read int|null $ENTITY_TYPE_ID
 * @property-read int|null $COUNTRY_ID
 * @property-read string|null $NAME
 * @property-read CarbonImmutable $DATE_CREATE
 * @property-read CarbonImmutable $DATE_MODIFY
 * @property-read int|null $CREATED_BY_ID
 * @property-read int|null $MODIFY_BY_ID
 * @property-read bool|null $ACTIVE
 * @property-read int|null $SORT
 * @property-read string|null $XML_ID
 */
class RequisitePresetItemResult extends AbstractCrmItem
{
}