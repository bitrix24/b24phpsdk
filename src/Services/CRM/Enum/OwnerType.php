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

namespace Bitrix24\SDK\Services\CRM\Enum;

enum OwnerType: int
{
    case lead = 1;
    case deal = 2;
    case contact = 3;
    case company = 4;
    case invoice = 5;
    case smart_invoice = 31;
    case estimate = 7;
    case requisite = 8;
    case all_inclusive = 130;
}