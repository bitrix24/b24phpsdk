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

final class FileItem implements AttachItemInterface
{
    private readonly string $link;

    private ?string $name = null;

    private ?int $size = null;

    private function __construct(string $link)
    {
        $this->link = $this->requireNonEmptyString($link, 'LINK');
    }

    public static function link(string $link): self
    {
        return new self($link);
    }

    public function name(string $name): self
    {
        $this->name = $this->requireNonEmptyString($name, 'NAME');

        return $this;
    }

    public function size(int $bytes): self
    {
        if ($bytes <= 0) {
            throw new \InvalidArgumentException('FILE size must be greater than zero');
        }

        $this->size = $bytes;

        return $this;
    }

    #[\Override]
    public function build(): array
    {
        return array_filter(
            [
                'LINK' => $this->link,
                'NAME' => $this->name,
                'SIZE' => $this->size,
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
}
