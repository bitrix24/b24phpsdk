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

namespace Bitrix24\SDK\Services\IMBot\Bot;

/**
 * Chat background for a chat-bot.
 *
 * @see https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/bots/bot-register.html
 */
enum BotBackground: string
{
    case azure = 'azure';
    case mint = 'mint';
    case steel = 'steel';
    case slate = 'slate';
    case teal = 'teal';
    case cornflower = 'cornflower';
    case sky = 'sky';
    case peach = 'peach';
    case frost = 'frost';
}
