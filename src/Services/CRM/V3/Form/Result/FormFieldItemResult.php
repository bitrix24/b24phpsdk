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

namespace Bitrix24\SDK\Services\CRM\V3\Form\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read string|null $name        CRM field code, e.g. LEAD_NAME
 * @property-read string|null $type        string, phone, email, date, list, ...
 * @property-read string|null $label
 * @property-read int|null    $sort
 * @property-read bool|null   $required
 * @property-read bool|null   $multiple
 * @property-read string|null $placeholder
 * @property-read string|null $valueType
 * @property-read string|null $value
 */
class FormFieldItemResult extends AbstractAnnotatedItem
{
}
