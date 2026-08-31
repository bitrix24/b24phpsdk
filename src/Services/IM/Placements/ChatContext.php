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

namespace Bitrix24\SDK\Services\IM\Placements;

/**
 * Chat type for which an IM widget is bound.
 *
 * Multi-value parameter: pass several cases to a placement options builder
 * and they are joined with `;` for the API.
 *
 * @link https://apidocs.bitrix24.com/api-reference/widgets/im/index.html
 */
enum ChatContext: string
{
    case USER = 'USER';
    case CHAT = 'CHAT';
    case LINES = 'LINES';
    case CRM = 'CRM';
    case ALL = 'ALL';
}
