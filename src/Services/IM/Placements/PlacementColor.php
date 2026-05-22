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
 * Color palette accepted by IM widget placement options (`IM_TEXTAREA`, `IM_SIDEBAR`).
 */
enum PlacementColor: string
{
    case Red = 'RED';
    case Green = 'GREEN';
    case Mint = 'MINT';
    case LightBlue = 'LIGHT_BLUE';
    case DarkBlue = 'DARK_BLUE';
    case Purple = 'PURPLE';
    case Aqua = 'AQUA';
    case Pink = 'PINK';
    case Lime = 'LIME';
    case Brown = 'BROWN';
    case Azure = 'AZURE';
    case Khaki = 'KHAKI';
    case Sand = 'SAND';
    case Orange = 'ORANGE';
    case Marengo = 'MARENGO';
    case Gray = 'GRAY';
    case Graphite = 'GRAPHITE';
}
