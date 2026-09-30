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

namespace Bitrix24\SDK\Services\Main\UserHistoryChangeField\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * Field descriptor properties may be omitted by partial selection.
 *
 * @property-read string|null $name
 * @property-read string|null $type
 * @property-read string|null $title
 * @property-read string|null $description
 * @property-read array|null $validationRules
 * @property-read array|null $requiredGroups
 * @property-read bool|null $filterable
 * @property-read bool|null $sortable
 * @property-read bool|null $editable
 * @property-read array|null $editableGroups
 * @property-read bool|null $multiple
 * @property-read string|null $elementType
 */
#[OpenApiEntity('bitrix.rest.dtofielddto')]
class UserHistoryChangeFieldItemResult extends AbstractAnnotatedItem
{
}
