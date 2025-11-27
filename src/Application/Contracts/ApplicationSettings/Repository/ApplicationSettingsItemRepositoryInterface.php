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
     *
     * @param ApplicationSettingsItemInterface $applicationSettingsItem
     */
    public function save(ApplicationSettingsItemInterface $applicationSettingsItem): void;

    /**
     * Delete application setting from persistence storage
     *
     * @param Uuid $uuid
     * @throws ApplicationSettingsItemNotFoundException
     * @throws InvalidArgumentException
     */
    public function delete(Uuid $uuid): void;

    /**
     * Find application setting by id
     *
     * @param Uuid $uuid
     * @return ApplicationSettingsItemInterface|null
     */
    public function findById(Uuid $uuid): ?ApplicationSettingsItemInterface;

    /**
     * Get application setting by id
     *
     * @param Uuid $uuid
     * @return ApplicationSettingsItemInterface
     * @throws ApplicationSettingsItemNotFoundException
     */
    public function getById(Uuid $uuid): ApplicationSettingsItemInterface;

    /**
     * Find all settings for specific application installation
     *
     * @param Uuid $applicationInstallationId
     * @return ApplicationSettingsItemInterface[]
     */
    public function findAllByApplicationInstallationId(Uuid $applicationInstallationId): array;

    /**
     * Find settings by application installation id and key
     *
     * Returns all settings with the same key across different scopes
     * (global, user-specific, department-specific)
     *
     * @param Uuid $applicationInstallationId
     * @param non-empty-string $key
     * @return ApplicationSettingsItemInterface[]
     * @throws InvalidArgumentException
     */
    public function findByApplicationInstallationIdAndKey(Uuid $applicationInstallationId, string $key): array;

    /**
     * Find setting by application installation id, key and user id
     *
     * @param Uuid $applicationInstallationId
     * @param non-empty-string $key
     * @param positive-int $bitrix24UserId
     * @return ApplicationSettingsItemInterface|null
     * @throws InvalidArgumentException
     */
    public function findByApplicationInstallationIdAndKeyAndUserId(
        Uuid $applicationInstallationId,
        string $key,
        int $bitrix24UserId
    ): ?ApplicationSettingsItemInterface;

    /**
     * Find setting by application installation id, key and department id
     *
     * @param Uuid $applicationInstallationId
     * @param non-empty-string $key
     * @param positive-int $bitrix24DepartmentId
     * @return ApplicationSettingsItemInterface|null
     * @throws InvalidArgumentException
     */
    public function findByApplicationInstallationIdAndKeyAndDepartmentId(
        Uuid $applicationInstallationId,
        string $key,
        int $bitrix24DepartmentId
    ): ?ApplicationSettingsItemInterface;

    /**
     * Find global setting (not user or department scoped) by application installation id and key
     *
     * @param Uuid $applicationInstallationId
     * @param non-empty-string $key
     * @return ApplicationSettingsItemInterface|null
     * @throws InvalidArgumentException
     */
    public function findGlobalByApplicationInstallationIdAndKey(
        Uuid $applicationInstallationId,
        string $key
    ): ?ApplicationSettingsItemInterface;
}
