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

namespace Bitrix24\SDK\Services\IMBot\ChatMessage\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result for imbot.v2.Chat.Message.get.
 *
 * Response shape: { result: { message: {...}, user: {...} } }
 */
class ChatMessageResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function message(): ChatMessageItemResult
    {
        return new ChatMessageItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()['message']
        );
    }

    /**
     * @return array<array-key, mixed>
     *
     * @throws BaseException
     */
    public function user(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['user'] ?? [];
    }
}
