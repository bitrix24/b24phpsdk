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

namespace Bitrix24\SDK\Services\IM\Message\Attach;

use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\DelimiterBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\MessageBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachBlockInterface;
use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachPayloadInterface;
use Bitrix24\SDK\Services\IM\Message\Attach\Enums\AttachColorToken;

final class Attach implements AttachPayloadInterface
{
    /** @var list<AttachBlockInterface> */
    private array $blocks = [];

    private ?int $id = null;

    private ?AttachColorToken $colorToken = null;

    private ?string $color = null;

    public static function create(): self
    {
        return new self();
    }

    public function id(int $id): self
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('Attach ID must be greater than zero');
        }

        $this->id = $id;

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
            throw new \InvalidArgumentException('Attach color must be a valid hex color in #RGB or #RRGGBB format');
        }

        $this->color = $hexColor;

        return $this;
    }

    public function add(AttachBlockInterface $attachBlock): self
    {
        $this->blocks[] = $attachBlock;

        return $this;
    }

    public function message(string $text): self
    {
        return $this->add(MessageBlock::text($text));
    }

    public function delimiter(?int $size = null, ?string $color = null): self
    {
        $delimiterBlock = DelimiterBlock::create();

        if ($size !== null) {
            $delimiterBlock->size($size);
        }

        if ($color !== null) {
            $delimiterBlock->color($color);
        }

        return $this->add($delimiterBlock);
    }

    #[\Override]
    public function build(): array
    {
        $blocks = array_map(
            static fn (AttachBlockInterface $attachBlock): array => $attachBlock->build(),
            $this->blocks
        );

        if ($this->id === null && !$this->colorToken instanceof \Bitrix24\SDK\Services\IM\Message\Attach\Enums\AttachColorToken && $this->color === null) {
            return $blocks;
        }

        return array_filter(
            [
                'ID' => $this->id,
                'COLOR_TOKEN' => $this->colorToken?->value,
                'COLOR' => $this->color,
                'BLOCKS' => $blocks,
            ],
            static fn (mixed $value): bool => $value !== null
        );
    }

    private function isValidHexColor(string $hexColor): bool
    {
        return (bool)preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $hexColor);
    }
}
