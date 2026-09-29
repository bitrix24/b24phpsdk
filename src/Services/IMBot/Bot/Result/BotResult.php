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

namespace Bitrix24\SDK\Services\IMBot\Bot\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result for imbot.v2.Bot.register / imbot.v2.Bot.get / imbot.v2.Bot.update.
 *
 * Response shape: { result: { bot: {...}, users: [...] } }
 */
class BotResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function bot(): BotItemResult
    {
        return new BotItemResult($this->getCoreResponse()->getResponseData()->getResult()['bot']);
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
