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

use Bitrix24\SDK\Core\Response\DTO\ResponseData;

/**
 * Single result item for a batch imbot.v2.Chat.Message.send call.
 *
 * Response shape per item: { id: int, uuidMap: {} }
 *
 * @see https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-send.html
 */
class ChatMessageSentBatchResult
{
    public function __construct(private readonly ResponseData $responseData)
    {
    }

    public function getResponseData(): ResponseData
    {
        return $this->responseData;
    }

    /**
     * Returns the ID of the sent message.
     */
    public function getId(): int
    {
        return (int)$this->responseData->getResult()['id'];
    }
}
