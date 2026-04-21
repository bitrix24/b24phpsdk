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

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class DialogMessagesResult extends AbstractResult
{
    /**
     * @return MessageItemResult[]
     * @throws BaseException
     */
    public function messages(): array
    {
        $messages = $this->payload()['messages'] ?? [];

        return array_map(
            static fn(array $message): MessageItemResult => new MessageItemResult($message),
            array_filter($messages, 'is_array')
        );
    }

    /**
     * @throws BaseException
     */
    public function chatId(): ?int
    {
        $chatId = $this->payload()['chat_id'] ?? null;

        return is_int($chatId) ? $chatId : null;
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
     * @return array<int, array<string, mixed>>
     * @throws BaseException
     */
    public function files(): array
    {
        return array_values(array_filter($this->payload()['files'] ?? [], 'is_array'));
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
