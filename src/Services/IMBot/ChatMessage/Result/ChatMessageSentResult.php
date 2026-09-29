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
 * Result for imbot.v2.Chat.Message.send.
 *
 * Response shape: { result: { id: int, uuidMap: {} } }
 */
class ChatMessageSentResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['id'];
    }

    /**
     * @return array<string, int>
     *
     * @throws BaseException
     */
    public function getUuidMap(): array
    {
        return (array)($this->getCoreResponse()->getResponseData()->getResult()['uuidMap'] ?? []);
    }
}
