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

namespace Bitrix24\SDK\Services\Note\Collection\Service;

/**
 * The `Pagination` object documented for `note.collection.list`: page size plus an
 * optional cursor pointing at the last item of the previous page.
 *
 * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-list.html
 */
final class CollectionListPagination
{
    public function __construct(
        private readonly int $limit = 50,
        private readonly ?CollectionListCursor $afterCursor = null,
    ) {
    }

    /**
     * @return array{limit?: int, afterCursor?: array{position: int, id: int}}
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'limit'       => $this->limit,
                'afterCursor' => $this->afterCursor?->toArray(),
            ],
            static fn (mixed $value): bool => $value !== null
        );
    }
}
