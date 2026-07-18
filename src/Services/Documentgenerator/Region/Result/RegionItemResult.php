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

namespace Bitrix24\SDK\Services\Documentgenerator\Region\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * Class RegionItemResult
 *
 * @property-read int         $id
 * @property-read string      $title
 * @property-read string      $languageId
 * @property-read string|null $formatDate
 * @property-read string|null $formatDatetime
 * @property-read string|null $formatName
 * @property-read array       $phrases
 */
class RegionItemResult extends AbstractAnnotatedItem
{
}
