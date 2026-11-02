<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Category\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read int $sort
 * @property-read int $entityTypeId
 * @property-read bool $isDefault
 * @property-read string|null $originId
 * @property-read string|null $originatorId
 */
class CategoryItemResult extends AbstractAnnotatedItem
{
}
