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

namespace Bitrix24\SDK\Core\Requests\Events;

use Symfony\Component\HttpFoundation\Request;

final class EventRequestPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function extract(Request $request): array
    {
        $payload = $request->request->all();
        if ($payload !== []) {
            return $payload;
        }

        $payload = [];
        parse_str($request->getContent(), $payload);

        return $payload;
    }
}
