<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Section\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int         $id
 * @property-read int         $iblockId
 * @property-read int|null    $iblockSectionId
 * @property-read string      $name
 * @property-read string|null $xmlId
 * @property-read string|null $code
 * @property-read int|null    $sort
 * @property-read bool|null   $active
 * @property-read string|null $description
 * @property-read string|null $descriptionType
 */
class SectionItemResult extends AbstractAnnotatedItem
{
}
