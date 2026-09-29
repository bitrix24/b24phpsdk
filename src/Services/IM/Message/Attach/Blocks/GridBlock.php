<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please see the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Message\Attach\Blocks;

use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachBlockInterface;
use Bitrix24\SDK\Services\IM\Message\Attach\Enums\AttachColorToken;
use Bitrix24\SDK\Services\IM\Message\Attach\Enums\GridDisplay;
use Bitrix24\SDK\Services\IM\Message\Attach\Items\GridItem;

final class GridBlock implements AttachBlockInterface
{
    /** @var list<GridItem> */
    private array $items = [];

    private ?int $width = null;

    private ?AttachColorToken $colorToken = null;

    private ?string $color = null;

    private function __construct(
        private readonly GridDisplay $display,
    ) {
    }

    public static function display(GridDisplay $gridDisplay): self
    {
        return new self($gridDisplay);
    }

    public function item(GridItem $gridItem): self
    {
        $this->items[] = $gridItem;

        return $this;
    }

    public function width(int $width): self
    {
        if ($width <= 0) {
            throw new \InvalidArgumentException('GRID width must be greater than zero');
        }

        $this->width = $width;

        return $this;
    }

    public function colorToken(AttachColorToken $attachColorToken): self
    {
        $this->colorToken = $attachColorToken;

        return $this;
    }

    public function color(string $hexColor): self
    {
        if (!$this->isValidHexColor($hexColor)) {
            throw new \InvalidArgumentException('GRID color must be a valid hex color in #RGB or #RRGGBB format');
        }

        $this->color = $hexColor;

        return $this;
    }

    #[\Override]
    public function build(): array
    {
        if ($this->items === []) {
            throw new \InvalidArgumentException('GRID block must contain at least one item');
        }

        return [
            'GRID' => array_map(
                fn (GridItem $gridItem): array => $this->buildItem($gridItem),
                $this->items
            ),
        ];
    }

    private function buildItem(GridItem $gridItem): array
    {
        $builtItem = $gridItem->build();

        if ($this->width !== null && !array_key_exists('WIDTH', $builtItem)) {
            $builtItem['WIDTH'] = $this->width;
        }

        if ($this->colorToken instanceof \Bitrix24\SDK\Services\IM\Message\Attach\Enums\AttachColorToken && !array_key_exists('COLOR_TOKEN', $builtItem)) {
            $builtItem['COLOR_TOKEN'] = $this->colorToken->value;
        }

        if ($this->color !== null && !array_key_exists('COLOR', $builtItem)) {
            $builtItem['COLOR'] = $this->color;
        }

        return $this->injectDisplay($builtItem);
    }

    /**
     * @param array<string, int|string> $item
     *
     * @return array<string, int|string>
     */
    private function injectDisplay(array $item): array
    {
        $withDisplay = [];

        foreach (['NAME', 'VALUE'] as $key) {
            if (array_key_exists($key, $item)) {
                $withDisplay[$key] = $item[$key];
                unset($item[$key]);
            }
        }

        $withDisplay['DISPLAY'] = $this->display->value;

        return $withDisplay + $item;
    }

    private function isValidHexColor(string $hexColor): bool
    {
        return (bool) preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $hexColor);
    }
}
