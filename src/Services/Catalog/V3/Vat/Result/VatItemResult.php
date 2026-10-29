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

namespace Bitrix24\SDK\Services\Catalog\V3\Vat\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Service\VatSelectBuilder;
use Carbon\CarbonImmutable;

/**
 * @property-read int                  $id
 * @property-read CarbonImmutable|null $timestampX
 * @property-read bool|null            $active
 * @property-read int|null             $sort
 * @property-read string|null          $name
 * @property-read float|null           $rate
 */
#[OpenApiEntity(
    entityKey:     'bitrix.catalog.vatdto',
    selectBuilder: VatSelectBuilder::class,
)]
class VatItemResult extends AbstractAnnotatedItem
{
}
