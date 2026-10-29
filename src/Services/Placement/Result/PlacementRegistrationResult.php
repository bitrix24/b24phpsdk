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

namespace Bitrix24\SDK\Services\Placement\Result;

use Bitrix24\SDK\Services\Placement\PlacementRegistrationStatus;

final readonly class PlacementRegistrationResult
{
    /** @param list<PlacementRegistrationItemResult> $items */
    public function __construct(private array $items)
    {
    }

    /** @return list<PlacementRegistrationItemResult> */
    public function getItems(): array
    {
        return $this->items;
    }

    public function isSuccess(): bool
    {
        return array_all($this->items, fn ($item) => $item->status !== PlacementRegistrationStatus::failed);
    }
}
