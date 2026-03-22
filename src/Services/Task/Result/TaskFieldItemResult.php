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

namespace Bitrix24\SDK\Services\Task\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read string|null $name
 * @property-read string|null $type
 * @property-read string|null $title
 * @property-read string|null $description
 * @property-read array|null $validationRules
 * @property-read array|null $requiredGroups
 * @property-read bool|null $filterable
 * @property-read bool|null $sortable
 * @property-read bool|null $editable
 * @property-read bool|null $multiple
 * @property-read string|null $elementType
 */
class TaskFieldItemResult extends AbstractItem
{
}
