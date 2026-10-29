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

namespace Bitrix24\SDK\Services\CRM\CallList\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int $ID
 * @property-read int $ENTITY_TYPE_ID
 * @property-read string|null $ENTITY_TYPE Available in crm.calllist.get; omitted by crm.calllist.list.
 * @property-read CarbonImmutable $DATE_CREATE
 * @property-read int|null $WEBFORM_ID
 * @property-read int $CREATED_BY_ID
 */
class CallListItemResult extends AbstractAnnotatedItem
{
}
