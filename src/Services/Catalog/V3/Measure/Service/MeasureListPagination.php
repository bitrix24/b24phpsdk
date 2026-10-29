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

namespace Bitrix24\SDK\Services\Catalog\V3\Measure\Service;

/**
 * The `pagination` object documented for `catalog.measure.list`.
 *
 * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-list-rest-v3.html
 */
final readonly class MeasureListPagination
{
    public function __construct(
        private ?int $page = null,
        private ?int $limit = null,
        private ?int $offset = null,
    ) {
    }

    /**
     * @return array{page?: int, limit?: int, offset?: int}
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'page'   => $this->page,
                'limit'  => $this->limit,
                'offset' => $this->offset,
            ],
            static fn (?int $value): bool => $value !== null
        );
    }
}
