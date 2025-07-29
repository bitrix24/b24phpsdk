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

namespace Bitrix24\SDK\Services\CRM\CallList\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;

/**
 * Class CallListItemResult
 *
 * @property-read int $ID
 * @property-read int $ENTITY_TYPE_ID
 * @property-read string $ENTITY_TYPE
 * @property-read CarbonImmutable $DATE_CREATE
 * @property-read int $WEBFORM_ID
 * @property-read int $CREATED_BY_ID
 */
class CallListItemResult extends AbstractCrmItem
{
}
