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

namespace Bitrix24\SDK\Services\IMBot\Chat;

/**
 * Available chat colours for imbot.v2.Chat.add.
 *
 * @see https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/chats/chat-add.html
 */
enum ChatColor: string
{
    case red = 'red';
    case green = 'green';
    case mint = 'mint';
    case lightBlue = 'lightBlue';
    case darkBlue = 'darkBlue';
    case purple = 'purple';
    case aqua = 'aqua';
    case pink = 'pink';
    case lime = 'lime';
    case brown = 'brown';
    case azure = 'azure';
    case khaki = 'khaki';
    case sand = 'sand';
    case marengo = 'marengo';
    case gray = 'gray';
    case graphite = 'graphite';
}
