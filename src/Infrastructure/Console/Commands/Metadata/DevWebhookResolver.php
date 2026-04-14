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

namespace Bitrix24\SDK\Infrastructure\Console\Commands\Metadata;

use InvalidArgumentException;

class DevWebhookResolver
{
    private const PLAYGROUND_WEBHOOK = 'BITRIX24_PHP_SDK_PLAYGROUND_WEBHOOK';
    private const WEBHOOK = 'BITRIX24_WEBHOOK';

    public function resolve(?string $explicitWebhook): string
    {
        $normalizedExplicitWebhook = trim((string)$explicitWebhook);
        if ($normalizedExplicitWebhook !== '') {
            return $normalizedExplicitWebhook;
        }

        foreach ([self::PLAYGROUND_WEBHOOK, self::WEBHOOK] as $envName) {
            $value = $this->readEnvironmentVariable($envName);
            if ($value !== null) {
                return $value;
            }
        }

        throw new InvalidArgumentException(
            'Webhook is not configured. Pass --webhook or set BITRIX24_WEBHOOK in tests/.env.local'
        );
    }

    private function readEnvironmentVariable(string $envName): ?string
    {
        $value = $_ENV[$envName] ?? $_SERVER[$envName] ?? getenv($envName);
        if ($value === false) {
            return null;
        }

        $normalizedValue = trim((string)$value);

        return $normalizedValue === '' ? null : $normalizedValue;
    }
}
