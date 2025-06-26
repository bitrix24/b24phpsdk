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

namespace Bitrix24\SDK\Services\CRM\Requisites\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;

/**
 * Class RequisitePresetFieldItemResult
 *
 * @property-read int|null $ID
 * @property-read string|null $FIELD_NAME
 * @property-read string|null $FIELD_TITLE
 * @property-read bool|null $IN_SHORT_LIST
 * @property-read int|null $SORT
 */
class RequisitePresetFieldItemResult extends AbstractCrmItem
{
}
