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

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Single message item returned by imbot.v2.Chat.Message.get / imbot.v2.Chat.Message.getContext.
 *
 * @property-read int $id
 * @property-read int $chatId
 * @property-read int $authorId
 * @property-read ?CarbonImmutable $date
 * @property-read string $text
 * @property-read bool $isSystem
 * @property-read string $uuid
 * @property-read bool $viewedByOthers
 */
class ChatMessageItemResult extends AbstractItem
{
}
