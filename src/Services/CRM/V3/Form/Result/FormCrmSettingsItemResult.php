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
 * @property-read int|null    $entityScheme                        see crm.form.configuration.get entitySchemes
 * @property-read string|null $duplicateMode
 * @property-read int|null    $dealCategoryId
 * @property-read bool|null   $dealDuplicateControlEnabled
 * @property-read int|null    $smartProcessCategoryId
 * @property-read bool|null   $smartProcessDuplicateControlEnabled
 */
class FormCrmSettingsItemResult extends AbstractAnnotatedItem
{
}
