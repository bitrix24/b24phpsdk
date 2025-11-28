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

namespace Bitrix24\SDK\Application\Contracts\ApplicationSettings\Repository;

use Bitrix24\SDK\Application\Contracts\ApplicationSettings\Entity\ApplicationSettingsItemInterface;
use Bitrix24\SDK\Application\Contracts\ApplicationSettings\Exceptions\ApplicationSettingsItemNotFoundException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

/**
 * Repository interface for ApplicationSettingsItem entity.
 *
 * Provides methods to:
 * - Persist application settings
 * - Delete settings (soft delete by marking as deleted)
 * - Find settings by various criteria
 */
interface ApplicationSettingsItemRepositoryInterface
{
    /**
     * Save application setting to persistence storage
     */
    public function save(ApplicationSettingsItemInterface $applicationSettingsItem): void;

    /**
     * Delete application setting from persistence storage
     *
     * @throws ApplicationSettingsItemNotFoundException
     * @throws InvalidArgumentException
     */
    public function delete(Uuid $uuid): void;

    /**
     * Find application setting by id
     */
    public function findById(Uuid $uuid): ?ApplicationSettingsItemInterface;

    /**
     * Get application setting by id
     *
     * @throws ApplicationSettingsItemNotFoundException
     */
    public function getById(Uuid $uuid): ApplicationSettingsItemInterface;

    /**
     * Find all settings for specific application installation
     *
     * @return ApplicationSettingsItemInterface[]
     */
    public function findAllByApplicationInstallationId(Uuid $uuid): array;

    /**
     * Find settings by application installation id and key
     *
     * Returns all settings with the same key across different scopes
     * (global, user-specific, department-specific)
     *
     * @param non-empty-string $key
     * @return ApplicationSettingsItemInterface[]
     * @throws InvalidArgumentException
     */
    public function findByApplicationInstallationIdAndKey(Uuid $uuid, string $key): array;
}
