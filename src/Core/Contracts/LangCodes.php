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

namespace Bitrix24\SDK\Core\Contracts;

/**
 * Language codes supported by Bitrix24 portal.
 *
 * Used in `LANG_ALL` section of `placement.bind` and similar API calls.
 *
 * @link https://apidocs.bitrix24.com/api-reference/widgets/placement-bind.html
 */
enum LangCodes: string
{
    case AR = 'ar';
    case DE = 'de';
    case EN = 'en';
    case FR = 'fr';
    case ID = 'id';
    case IT = 'it';
    case JA = 'ja';
    case KO = 'ko';
    case MS = 'ms';
    case PL = 'pl';
    case PT = 'pt';
    case RU = 'ru';
    case SK = 'sk';
    case TH = 'th';
    case TR = 'tr';
    case UA = 'ua';
    case VI = 'vi';
    case ZH = 'zh';
}

