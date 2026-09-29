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

namespace Bitrix24\SDK\Services\IM\Message\Attach\Blocks;

use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachBlockInterface;

final class DelimiterBlock implements AttachBlockInterface
{
    private ?int $size = null;

    private ?string $color = null;

    public static function create(): self
    {
        return new self();
    }

    public function size(int $size): self
    {
        if ($size <= 0) {
            throw new \InvalidArgumentException('Delimiter size must be greater than zero');
        }

        $this->size = $size;

        return $this;
    }

    public function color(string $hexColor): self
    {
        if (!$this->isValidHexColor($hexColor)) {
            throw new \InvalidArgumentException('Delimiter color must be a valid hex color in #RGB or #RRGGBB format');
        }

        $this->color = $hexColor;

        return $this;
    }

    private function isValidHexColor(string $hexColor): bool
    {
        return (bool)preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $hexColor);
    }

    #[\Override]
    public function build(): array
    {
        return [
            'DELIMITER' => array_filter(
                [
                    'SIZE' => $this->size,
                    'COLOR' => $this->color,
                ],
                static fn (mixed $value): bool => $value !== null
            ),
        ];
    }
}
