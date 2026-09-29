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
 * Controls extranet-user availability for a placement widget.
 *
 * @link https://apidocs.bitrix24.com/api-reference/chats/widgets/placement-info.html
 */
enum ExtranetAvailability: string
{
    /** Not available to extranet users (default). */
    case No = 'N';

    /** Available to extranet users. */
    case Yes = 'Y';
}

