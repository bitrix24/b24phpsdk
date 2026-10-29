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

namespace Bitrix24\SDK\Services\CRM\V3\FormOpenLine\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * Open Channel; bound tells whether the requested form is used in it, welcomeForm* describe the welcome form.
 *
 * @property-read int|null    $formId
 * @property-read int         $id
 * @property-read string|null $name
 * @property-read bool|null   $bound
 * @property-read bool|null   $welcomeFormEnabled
 * @property-read int|null    $welcomeFormId
 * @property-read bool|null   $welcomeFormDelayed
 * @property-read string|null $previewUrl
 */
#[OpenApiEntity(entityKey: 'bitrix.crm.openlinedto')]
class OpenLineItemResult extends AbstractAnnotatedItem
{
}
