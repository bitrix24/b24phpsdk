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
 * Chat-bot type.
 *
 * @see https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/bots/bot-register.html
 */
enum BotType: string
{
    case bot = 'bot';
    case network = 'network';
    case openline = 'openline';
    case supervisor = 'supervisor';
    case personal = 'personal';
}
