<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Chat;

enum ChatType: string
{
    case Open = 'OPEN';
    // API value 'CHAT' denotes a closed / non-public chat.
    case Closed = 'CHAT';
}
