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

namespace Bitrix24\SDK\Services\IM\Placements;

use Bitrix24\SDK\Core\Contracts\LangCodes;

final readonly class PlacementLangMap
{
    /**
     * @param array<value-of<LangCodes>, PlacementLangItem> $items
     */
    private function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function with(LangCodes $langCodes, PlacementLangItem $placementLangItem): self
    {
        $items = $this->items;
        $items[$langCodes->value] = $placementLangItem;

        return new self($items);
    }

    /**
     * @return array<string, array{TITLE: string, DESCRIPTION?: string, GROUP_NAME?: string}>
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->items as $langCode => $placementLangItem) {
            $result[$langCode] = $placementLangItem->toArray();
        }

        return $result;
    }
}
