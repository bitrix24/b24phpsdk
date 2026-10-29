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

namespace Bitrix24\SDK\Services\Note\File\Service;

/**
 * Cursor pagination for `note.file.list`. Cursor data is an opaque API value.
 */
final readonly class FileListPagination
{
    /**
     * @param array<string, mixed>|null $afterCursor
     */
    public function __construct(
        private int $limit = 50,
        private ?array $afterCursor = null,
    ) {
    }

    /**
     * @return array{limit: int, afterCursor?: array<string, mixed>}
     */
    public function toArray(): array
    {
        return array_filter(
            ['limit' => $this->limit, 'afterCursor' => $this->afterCursor],
            static fn (mixed $value): bool => $value !== null
        );
    }
}
