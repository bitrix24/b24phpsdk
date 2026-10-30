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

namespace Bitrix24\SDK\Services\CRM\V3\FormField\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * CRM field descriptor (`bitrix.crm.crmfieldmetadatadto`): unlike the generic REST v3 descriptor it has
 * no editableGroups and reports date fields as `date-time`.
 *
 * @property-read string      $name
 * @property-read string      $type
 * @property-read string|null $elementType
 * @property-read bool        $multiple
 * @property-read string      $title
 * @property-read string|null $description
 * @property-read array|null  $validationRules
 * @property-read array|null  $requiredGroups
 * @property-read bool        $filterable
 * @property-read bool        $sortable
 * @property-read bool        $editable
 */
class FormFieldMetadataItemResult extends AbstractAnnotatedItem
{
}
