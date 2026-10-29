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

namespace Bitrix24\SDK\Services\Catalog\V3\Section\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\Catalog\V3\Section\Service\SectionSelectBuilder;

/**
 * @property-read int         $id
 * @property-read int|null    $iblockId
 * @property-read string|null $name
 * @property-read int|null    $iblockSectionId  0 for a root section
 * @property-read bool|null   $active           REST v3 returns Y/N
 * @property-read string|null $code
 * @property-read string|null $xmlId
 * @property-read int|null    $sort
 * @property-read string|null $description
 * @property-read string|null $descriptionType  text or html
 */
#[OpenApiEntity(
    entityKey:     'bitrix.catalog.sectiondto',
    selectBuilder: SectionSelectBuilder::class,
)]
class SectionItemResult extends AbstractAnnotatedItem
{
}
