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
 * Class RequisiteLinkItemResult
 *
 * @property-read int $ENTITY_TYPE_ID
 * @property-read int $ENTITY_ID
 * @property-read int $REQUISITE_ID
 * @property-read int $BANK_DETAIL_ID
 * @property-read int $MC_REQUISITE_ID
 * @property-read int $MC_BANK_DETAIL_ID
 */
class RequisiteLinkItemResult extends AbstractCrmItem
{
}
