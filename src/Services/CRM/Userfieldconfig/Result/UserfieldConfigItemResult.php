<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Userfieldconfig\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read positive-int $id
 * @property-read non-empty-string $entityId
 * @property-read non-empty-string $fieldName
 * @property-read non-empty-string $userTypeId
 * @property-read string|null $xmlId
 * @property-read int $sort
 * @property-read bool $multiple
 * @property-read bool $mandatory
 * @property-read string $showFilter
 * @property-read bool $showInList
 * @property-read bool $editInList
 * @property-read bool $isSearchable
 * @property-read array $settings
 * @property-read array $languageId
 * @property-read array $editFormLabel
 * @property-read array $listColumnLabel
 * @property-read array $listFilterLabel
 * @property-read array $errorMessage
 * @property-read array $helpMessage
 * @property-read array<UserfieldConfigEnumItemResult>|null $enum
 */
class UserfieldConfigItemResult extends AbstractAnnotatedItem
{
}
