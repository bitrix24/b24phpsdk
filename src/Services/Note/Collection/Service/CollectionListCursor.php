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
 * The `afterCursor` object documented for `note.collection.list`.
 *
 * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-list.html
 */
final class CollectionListCursor
{
    public function __construct(
        private readonly int $position,
        private readonly int $id,
    ) {
    }

    /**
     * @return array{position: int, id: int}
     */
    public function toArray(): array
    {
        return [
            'position' => $this->position,
            'id'       => $this->id,
        ];
    }

    /**
     * @param array{position: int, id: int} $data
     */
    public static function fromArray(array $data): self
    {
        return new self((int)$data['position'], (int)$data['id']);
    }
}
