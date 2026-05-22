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

namespace Bitrix24\SDK\Services\IM\Notify\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class NotifyHistorySearchResult extends AbstractResult
{
    /**
     * @return NotifyItemResult[]
     * @throws BaseException
     */
    public function notifications(): array
    {
        return array_map(
            static fn(array $item): NotifyItemResult => new NotifyItemResult($item),
            array_filter($this->payload()['notifications'] ?? [], 'is_array')
        );
    }

    /**
     * @throws BaseException
     */
    public function totalResults(): int
    {
        return (int)($this->payload()['total_results'] ?? 0);
    }

    /**
     * @throws BaseException
     */
    public function chatId(): int
    {
        return (int)($this->payload()['chat_id'] ?? 0);
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws BaseException
     */
    public function users(): array
    {
        return array_values(array_filter($this->payload()['users'] ?? [], 'is_array'));
    }

    /**
     * @return array<string, mixed>
     * @throws BaseException
     */
    private function payload(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        return array_is_list($result) && is_array($result[0] ?? null) ? $result[0] : $result;
    }
}
