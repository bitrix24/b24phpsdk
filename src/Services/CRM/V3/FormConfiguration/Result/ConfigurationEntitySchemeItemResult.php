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

namespace Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * CRM scheme: which CRM objects a submitted form creates (crm.entityScheme of a form).
 *
 * @property-read int|null           $id
 * @property-read string|null        $name
 * @property-read string|null        $description
 * @property-read array<string>|null $entityNames
 * @property-read string|null        $mainEntityName
 * @property-read bool|null          $hasInvoice
 * @property-read bool|null          $dynamic
 */
class ConfigurationEntitySchemeItemResult extends AbstractAnnotatedItem
{
}
