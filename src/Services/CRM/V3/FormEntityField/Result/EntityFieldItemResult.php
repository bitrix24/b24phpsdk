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

namespace Bitrix24\SDK\Services\CRM\V3\FormEntityField\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * CRM entity field that can be added to a form; items lists the values of list fields.
 *
 * @property-read string|null                             $name
 * @property-read string|null                             $label
 * @property-read string|null                             $type
 * @property-read bool|null                               $required
 * @property-read bool|null                               $multiple
 * @property-read string|null                             $entityName
 * @property-read string|null                             $entityLabel
 * @property-read array<EntityFieldOptionItemResult>|null $items
 */
#[OpenApiEntity(entityKey: 'bitrix.crm.entityfielddto')]
class EntityFieldItemResult extends AbstractAnnotatedItem
{
}
