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

namespace Bitrix24\SDK\Core\Exceptions;

use Carbon\CarbonImmutable;

/**
 * Optimistic concurrency failure in REST v3: the entity changed after it was read
 * (e.g. CRM_FORM_VERSION_MISMATCH). Re-read the entity, or retry with getCurrentVersion().
 */
class VersionMismatchException extends BaseException
{
    public function __construct(
        string $message = '',
        private readonly ?CarbonImmutable $currentVersion = null,
        int $code = 0,
        ?\Throwable $throwable = null,
    ) {
        parent::__construct($message, $code, $throwable);
    }

    public static function parseVersion(mixed $version): ?CarbonImmutable
    {
        if (!is_string($version) || $version === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($version);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Current entity version reported by the portal, if any.
     */
    public function getCurrentVersion(): ?CarbonImmutable
    {
        return $this->currentVersion;
    }
}
