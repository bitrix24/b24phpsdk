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

namespace Bitrix24\SDK\Services\Placement;

/**
 * User role filter for IM placement widgets.
 *
 * Controls which user roles can see the placement widget.
 *
 * @link https://apidocs.bitrix24.com/api-reference/chats/widgets/placement-info.html
 */
enum Role: string
{
    /** Visible to all users (default). */
    case USER = 'USER';

    /** Visible to portal administrators only. */
    case ADMIN = 'ADMIN';
}

