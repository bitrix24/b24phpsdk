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

namespace Bitrix24\SDK\Services\CRM\V3\FormSiteButton\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * Website widget; bound tells whether the requested form is attached to it, formsId lists all attached forms.
 *
 * @property-read int|null        $formId
 * @property-read int             $id
 * @property-read string|null     $name
 * @property-read bool|null       $system
 * @property-read bool|null       $bound
 * @property-read array<int>|null $formsId
 * @property-read string|null     $previewUrl
 */
#[OpenApiEntity(entityKey: 'bitrix.crm.sitebuttondto')]
class SiteButtonItemResult extends AbstractAnnotatedItem
{
}
