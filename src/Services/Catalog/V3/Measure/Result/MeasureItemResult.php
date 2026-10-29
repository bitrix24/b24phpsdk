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

namespace Bitrix24\SDK\Services\Catalog\V3\Measure\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Service\MeasureSelectBuilder;

/**
 * @property-read int         $id
 * @property-read int|null    $code
 * @property-read string|null $measureTitle
 * @property-read string|null $symbol
 * @property-read string|null $symbolIntl
 * @property-read string|null $symbolLetterIntl
 * @property-read bool|null   $isDefault
 */
#[OpenApiEntity(
    entityKey:     'bitrix.catalog.measuredto',
    selectBuilder: MeasureSelectBuilder::class,
)]
class MeasureItemResult extends AbstractAnnotatedItem
{
}
