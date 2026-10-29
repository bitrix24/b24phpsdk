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

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * Directories for building a CRM form: CRM schemes, categories, languages, currencies, field types, limits.
 *
 * @property-read array<ConfigurationEntitySchemeItemResult>|null $entitySchemes
 * @property-read array<NamedCodeItemResult>|null                 $duplicateModes
 * @property-read ConfigurationLeadItemResult|null                $lead
 * @property-read array<NamedIdItemResult>|null                   $dealCategories
 * @property-read array<ConfigurationSmartProcessItemResult>|null $smartProcesses
 * @property-read array<NamedCodeItemResult>|null                 $languages
 * @property-read array<ConfigurationCurrencyItemResult>|null     $currencies
 * @property-read array<ConfigurationFieldTypeItemResult>|null    $fieldTypes
 * @property-read array<NamedIdItemResult>|null                   $paySystems
 * @property-read ConfigurationPermissionsItemResult|null         $permissions
 * @property-read ConfigurationLimitsItemResult|null              $limits
 * @property-read ConfigurationProductItemResult|null             $product
 * @property-read string|null                                     $region
 */
#[OpenApiEntity(entityKey: 'bitrix.crm.configurationdto')]
class ConfigurationItemResult extends AbstractAnnotatedItem
{
}
