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

namespace Bitrix24\SDK\Services\SonetGroup\Common;

/**
 * Active workgroup member role.
 *
 * @see https://apidocs.bitrix24.com/api-reference/sonet-group/members/sonet-group-user-get.html
 */
enum MemberRole: string
{
    case owner = 'A';
    case moderator = 'E';
    case member = 'K';
}
