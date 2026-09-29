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
use Bitrix24\SDK\Services\IM\Message\Attach\Items\FileItem;

final class FileBlock implements AttachBlockInterface
{
    /** @var list<FileItem> */
    private array $items = [];

    public static function create(): self
    {
        return new self();
    }

    public function item(FileItem $fileItem): self
    {
        $this->items[] = $fileItem;

        return $this;
    }

    #[\Override]
    public function build(): array
    {
        if ($this->items === []) {
            throw new \InvalidArgumentException('FILE block must contain at least one item');
        }

        return [
            'FILE' => array_map(
                static fn (FileItem $fileItem): array => $fileItem->build(),
                $this->items
            ),
        ];
    }
}
