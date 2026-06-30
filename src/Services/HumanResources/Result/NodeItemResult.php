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

namespace Bitrix24\SDK\Services\HumanResources\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int|null             $id
 * @property-read string|null          $name
 * @property-read string|null          $type
 * @property-read int|null             $structureId
 * @property-read int|null             $parentId
 * @property-read string|null          $description
 * @property-read string|null          $accessCode
 * @property-read int|null             $userCount
 * @property-read string|null          $colorName
 * @property-read string|null          $xmlId
 * @property-read CarbonImmutable|null $createdAt
 * @property-read CarbonImmutable|null $updatedAt
 * @property-read array|null           $members
 */
class NodeItemResult extends AbstractAnnotatedItem
{
}
