<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Type\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * @property-read non-negative-int $id
 * @property-read string $title
 * @property-read string $code
 * @property-read non-negative-int $createdBy
 * @property-read non-negative-int $entityTypeId
 * @property-read non-negative-int|null $customSectionId
 * @property-read bool $isCategoriesEnabled
 * @property-read bool $isStagesEnabled
 * @property-read bool $isBeginCloseDatesEnabled
 * @property-read bool $isClientEnabled
 * @property-read bool $isUseInUserfieldEnabled
 * @property-read bool $isLinkWithProductsEnabled
 * @property-read bool $isMycompanyEnabled
 * @property-read bool $isDocumentsEnabled
 * @property-read bool $isSourceEnabled
 * @property-read bool $isObserversEnabled
 * @property-read bool $isRecurringEnabled
 * @property-read bool $isRecyclebinEnabled
 * @property-read bool $isAutomationEnabled
 * @property-read bool $isBizProcEnabled
 * @property-read bool $isSetOpenPermissions
 * @property-read bool $isPaymentsEnabled
 * @property-read bool $isCountersEnabled
 * @property-read CarbonImmutable $createdTime
 * @property-read CarbonImmutable $updatedTime
 * @property-read int $updatedBy
 * @property-read bool $isInitialized
 * @property-read array $relations
 * @property-read array $linkedUserFields
 * @property-read array $customSections
 */
class TypeItemResult extends AbstractItem
{
}
