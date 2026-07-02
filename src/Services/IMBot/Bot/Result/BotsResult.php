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
 * Result for imbot.v2.Bot.list.
 *
 * Response shape: { result: { bots: [...], users: [...], hasNextPage: bool } }
 */
class BotsResult extends AbstractResult
{
    /**
     * @return BotItemResult[]
     *
     * @throws BaseException
     */
    public function bots(): array
    {
        return array_map(
            static fn (array $bot): BotItemResult => new BotItemResult($bot),
            $this->getCoreResponse()->getResponseData()->getResult()['bots'] ?? []
        );
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

    /**
     * @throws BaseException
     */
    public function hasNextPage(): bool
    {
        return (bool)($this->getCoreResponse()->getResponseData()->getResult()['hasNextPage'] ?? false);
    }
}
