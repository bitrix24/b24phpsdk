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

namespace Bitrix24\SDK\Services\Entity\Entity\Result;

use Bitrix24\SDK\Services\Catalog\Common\ProductType;
use Bitrix24\SDK\Services\Catalog\Common\Result\AbstractCatalogItem;
use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;
use Money\Currency;
use Money\Money;

/**
 * @property-read int $ID
 * @property-read string $IBLOCK_TYPE_ID
 * @property-read string $ENTITY
 * @property-read string $NAME
 */
class EntityItemResult extends AbstractCrmItem
{
}