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

enum AddressType: int
{
    case delivery = 11;
    case actual = 1;
    case legal = 6;
    case registration = 4;
    case correspondence = 8;
    case beneficiary = 9;
}