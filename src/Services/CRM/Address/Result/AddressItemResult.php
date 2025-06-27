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

namespace Bitrix24\SDK\Services\CRM\Address\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;

/**
 * Class AddressItemResult
 *
 * @property-read int $TYPE_ID
 * @property-read int $ENTITY_TYPE_ID
 * @property-read int $ENTITY_ID
 * @property-read string|null $ADDRESS_1
 * @property-read string|null $ADDRESS_2
 * @property-read string|null $CITY
 * @property-read string|null $POSTAL_CODE
 * @property-read string|null $REGION
 * @property-read string|null $PROVINCE
 * @property-read string|null $COUNTRY
 * @property-read string|null $COUNTRY_CODE
 * @property-read int|null $LOC_ADDR_ID
 * @property-read int|null $ANCHOR_TYPE_ID
 * @property-read int|null $ANCHOR_ID
 */
class AddressItemResult extends AbstractCrmItem
{
}
