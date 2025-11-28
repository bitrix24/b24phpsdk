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

namespace Bitrix24\SDK\Application\Contracts\ApplicationSettings\Entity;

/**
 * Application Setting Status enum.
 *
 * Represents the lifecycle status of an application setting.
 * Uses soft-delete pattern to maintain history and enable recovery.
 */
enum ApplicationSettingStatus: string
{
    /**
     * Active setting - available for use.
     */
    case active = 'active';

    /**
     * Deleted setting - soft-deleted, hidden from normal queries.
     */
    case deleted = 'deleted';

    /**
     * Check if status is active.
     */
    public function isActive(): bool
    {
        return self::active === $this;
    }

    /**
     * Check if status is deleted.
     */
    public function isDeleted(): bool
    {
        return self::deleted === $this;
    }
}
