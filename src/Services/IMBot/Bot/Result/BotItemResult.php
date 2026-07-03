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

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Single chat-bot item returned by imbot.v2.Bot.* methods.
 *
 * @property-read int $id
 * @property-read string $code
 * @property-read string $type
 * @property-read bool $isHidden
 * @property-read bool $isSupportOpenline
 * @property-read bool $isReactionsEnabled
 * @property-read ?string $backgroundId
 * @property-read string $language
 * @property-read string $moduleId
 * @property-read string $eventMode
 * @property-read int $countMessage
 * @property-read int $countCommand
 * @property-read int $countChat
 * @property-read int $countUser
 */
class BotItemResult extends AbstractItem
{
}
