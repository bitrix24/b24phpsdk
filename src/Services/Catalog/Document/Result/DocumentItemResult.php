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

namespace Bitrix24\SDK\Services\Catalog\Document\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int                  $id
 * @property-read string               $docType
 * @property-read string               $currency
 * @property-read int                  $responsibleId
 * @property-read string|null          $siteId
 * @property-read CarbonImmutable|null $dateDocument
 * @property-read CarbonImmutable|null $dateCreate
 * @property-read CarbonImmutable|null $dateModify
 * @property-read CarbonImmutable|null $dateStatus
 * @property-read string|null          $title
 * @property-read string|null          $commentary
 * @property-read float|null           $total
 * @property-read string|null          $docNumber
 * @property-read int|null             $createdBy
 * @property-read int|null             $modifiedBy
 * @property-read string|null          $status
 * @property-read int|null             $statusBy
 */
class DocumentItemResult extends AbstractItem
{
}
