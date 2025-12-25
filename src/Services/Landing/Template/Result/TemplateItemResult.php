<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Landing\Template\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * @property-read non-negative-int $ID Template identifier
 * @property-read bool $ACTIVE Template activity
 * @property-read non-negative-int $AREA_COUNT Number of areas besides content
 * @property-read non-negative-int $SORT Sorting
 * @property-read string $TITLE Title
 * @property-read string $XML_ID External code
 * @property-read string $CONTENT Template markup
 * @property-read non-negative-int $CREATED_BY_ID Identifier of the user who created the template
 * @property-read non-negative-int $MODIFIED_BY_ID Identifier of the user who modified the template
 * @property-read CarbonImmutable $DATE_CREATE Creation date
 * @property-read CarbonImmutable $DATE_MODIFY Modification date
 */
class TemplateItemResult extends AbstractItem
{
}
