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

namespace Bitrix24\SDK\Services\Catalog\ProductProperty\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * Class ProductPropertyItemResult
 *
 * @property-read bool            $active
 * @property-read string|null     $code
 * @property-read int             $colCount
 * @property-read string|null     $defaultValue
 * @property-read string|null     $fileType
 * @property-read bool            $filtrable
 * @property-read string|null     $hint
 * @property-read int             $iblockId
 * @property-read int             $id
 * @property-read bool            $isRequired
 * @property-read int|null        $linkIblockId
 * @property-read string          $listType
 * @property-read bool            $multiple
 * @property-read int|null        $multipleCnt
 * @property-read string          $name
 * @property-read string          $propertyType
 * @property-read int             $rowCount
 * @property-read bool            $searchable
 * @property-read int|null        $sort
 * @property-read CarbonImmutable $timestampX
 * @property-read string|null     $userType
 * @property-read array|null      $userTypeSettings
 * @property-read bool|null       $withDescription
 * @property-read string|null     $xmlId
 */
class ProductPropertyItemResult extends AbstractAnnotatedItem
{
}
