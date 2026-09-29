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
 * The `afterCursor` object documented for `call.followup.list`.
 *
 * @link https://apidocs.bitrix24.com/api-reference/telephony/follow-up/call-followup-list.html
 */
final readonly class FollowUpListCursor
{
    public function __construct(
        private string $startDate,
        private int $id,
    ) {
    }

    /**
     * @return array{startDate: string, id: int}
     */
    public function toArray(): array
    {
        return [
            'startDate' => $this->startDate,
            'id'        => $this->id,
        ];
    }

    /**
     * @param array{startDate: string, id: int} $data
     */
    public static function fromArray(array $data): self
    {
        return new self((string)$data['startDate'], (int)$data['id']);
    }
}
