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

namespace Bitrix24\SDK\Core\ValueObjects;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;

/** Converts Stage 1 inputs to the primitive values expected by REST. */
final class ValueObjectResolver
{
    /** @throws InvalidArgumentException */
    public static function resolveUrl(string|Url $value): string
    {
        return ($value instanceof Url ? $value : new Url($value))->getUrl();
    }

    /**
     * @param array<string, string>|LocalizedString $value
     * @return array<string, string>
     */
    public static function resolveLocalizedString(array|LocalizedString $value): array
    {
        return $value instanceof LocalizedString ? $value->toArray() : $value;
    }
}
