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

namespace Bitrix24\SDK\Services\IMBot\Chat\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result for imbot.v2.Chat.add / imbot.v2.Chat.get / imbot.v2.Chat.update.
 *
 * Response shape: { result: { chat: {...}, users: [...] } }
 */
class ChatResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function chat(): ChatItemResult
    {
        return new ChatItemResult($this->getCoreResponse()->getResponseData()->getResult()['chat']);
    }

    /**
     * @return array<array-key, mixed>
     *
     * @throws BaseException
     */
    public function users(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['users'] ?? [];
    }
}
