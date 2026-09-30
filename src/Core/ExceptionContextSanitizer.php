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

namespace Bitrix24\SDK\Core;

/** @internal */
final class ExceptionContextSanitizer
{
    public static function redactMessage(string $message): string
    {
        return preg_replace_callback(
            '~([?&])([^?&=\s#\x22\x27<>]+)=([^&\s#\x22\x27<>]*)~',
            static function (array $matches): string {
                $name = strtolower(rawurldecode($matches[2]));
                if (in_array($name, ['client_secret', 'refresh_token', 'access_token', 'auth'], true)) {
                    return $matches[1] . $matches[2] . '=[REDACTED]';
                }

                return $matches[0];
            },
            $message
        ) ?? 'Exception message could not be safely redacted';
    }

    /**
     * @param list<array<string, mixed>> $trace
     * @return list<array<string, int|string>>
     */
    public static function sanitizeTrace(array $trace): array
    {
        $safeTrace = [];
        foreach ($trace as $frame) {
            $safeFrame = [];
            foreach (['file', 'line', 'class', 'function', 'type'] as $key) {
                $value = $frame[$key] ?? null;
                if (is_string($value)) {
                    $safeFrame[$key] = self::redactMessage($value);
                } elseif (is_int($value)) {
                    $safeFrame[$key] = $value;
                }
            }

            $safeTrace[] = $safeFrame;
        }

        return $safeTrace;
    }
}
