<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Telephony\FollowUp\Service;

/**
 * The `pagination` object documented for `call.followup.list`: page size plus an optional
 * cursor pointing at the last item of the previous page.
 *
 * @link https://apidocs.bitrix24.com/api-reference/telephony/follow-up/call-followup-list.html
 */
final readonly class FollowUpListPagination
{
    public function __construct(
        private int $limit = 50,
        private ?FollowUpListCursor $afterCursor = null,
    ) {
    }

    /**
     * @return array{limit?: int, afterCursor?: array{startDate: string, id: int}}
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
