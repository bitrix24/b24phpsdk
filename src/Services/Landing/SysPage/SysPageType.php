<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Landing\SysPage;

enum SysPageType: string
{
    case mainpage = 'mainpage';
    case catalog = 'catalog';
    case personal = 'personal';
    case cart = 'cart';
    case order = 'order';
    case payment = 'payment';
    case compare = 'compare';
}