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

namespace Bitrix24\SDK\Services\CRM\V3\Form\Service;

/**
 * Pagination of crm.form.sitebutton.list and crm.form.openline.list: only limit and offset are accepted
 * (a `page` key is rejected with BITRIX_REST_V3_EXCEPTION_INVALIDPAGINATIONEXCEPTION).
 */
final readonly class FormPlacementListPagination
{
    public function __construct(
        private ?int $limit = null,
        private ?int $offset = null,
    ) {
    }

    /**
     * @return array{limit?: int, offset?: int}
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'limit'  => $this->limit,
                'offset' => $this->offset,
            ],
            static fn (?int $value): bool => $value !== null
        );
    }
}
