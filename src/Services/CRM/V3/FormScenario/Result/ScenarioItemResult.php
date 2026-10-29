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

namespace Bitrix24\SDK\Services\CRM\V3\FormScenario\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * Form scenario (template) shown in the form builder; categoryId refers to a scenario category.
 *
 * @property-read string|null        $id
 * @property-read string|null        $title
 * @property-read string|null        $description
 * @property-read string|null        $categoryId
 * @property-read string|null        $iconUrl
 * @property-read array<string>|null $sections
 * @property-read array<string>|null $expertSections
 * @property-read string|null        $defaultSection
 * @property-read bool|null          $openable
 * @property-read bool|null          $soon
 * @property-read array|null         $actions
 */
#[OpenApiEntity(entityKey: 'bitrix.crm.scenariodto')]
class ScenarioItemResult extends AbstractAnnotatedItem
{
}
