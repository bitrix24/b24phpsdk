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

namespace Bitrix24\SDK\Services\IM\Message\Attach\Items;

use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachItemInterface;
use Bitrix24\SDK\Services\IM\Message\Attach\Enums\AttachColorToken;

final class GridItem implements AttachItemInterface
{
    private readonly string $name;

    private ?string $value = null;

    private ?string $link = null;

    private ?int $userId = null;

    private ?int $chatId = null;

    private ?int $width = null;

    private ?int $height = null;

    private ?AttachColorToken $colorToken = null;

    private ?string $color = null;

    private function __construct(string $name)
    {
        $this->name = $this->requireNonEmptyString($name, 'NAME');
    }

    public static function name(string $name): self
    {
        return new self($name);
    }

    public function value(string $value): self
    {
        $this->value = $this->requireNonEmptyString($value, 'VALUE');

        return $this;
    }

    public function link(string $url): self
    {
        $this->link = $this->requireNonEmptyString($url, 'LINK');

        return $this;
    }

    public function userId(int $userId): self
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('USER_ID must be greater than zero');
        }

        $this->userId = $userId;

        return $this;
    }

    public function chatId(int $chatId): self
    {
        if ($chatId <= 0) {
            throw new \InvalidArgumentException('CHAT_ID must be greater than zero');
        }

        $this->chatId = $chatId;

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

    public function height(int $height): self
    {
        if ($height <= 0) {
            throw new \InvalidArgumentException('GRID height must be greater than zero');
        }

        $this->height = $height;

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
        return array_filter(
            [
                'NAME' => $this->name,
                'VALUE' => $this->value,
                'LINK' => $this->link,
                'USER_ID' => $this->userId,
                'CHAT_ID' => $this->chatId,
                'WIDTH' => $this->width,
                'HEIGHT' => $this->height,
                'COLOR_TOKEN' => $this->colorToken?->value,
                'COLOR' => $this->color,
            ],
            static fn (mixed $value): bool => $value !== null
        );
    }

    private function requireNonEmptyString(string $value, string $fieldName): string
    {
        if ($value === '') {
            throw new \InvalidArgumentException(sprintf('%s must not be empty', $fieldName));
        }

        return $value;
    }

    private function isValidHexColor(string $hexColor): bool
    {
        return (bool) preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $hexColor);
    }
}
