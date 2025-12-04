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

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * Interface for ApplicationSetting entity.
 *
 * This entity represents a single application setting that can be scoped to:
 * - Global level (application-wide)
 * - User level (specific Bitrix24 user)
 * - Department level (specific Bitrix24 department)
 */
interface ApplicationSettingsItemInterface
{
    /**
     * @return Uuid unique setting id
     */
    public function getId(): Uuid;

    /**
     * @return Uuid application installation id this setting belongs to
     */
    public function getApplicationInstallationId(): Uuid;

    /**
     * @return non-empty-string setting key (e.g., "theme.color", "notification.enabled")
     */
    public function getKey(): string;

    /**
     * @return string setting value (stored as string, can be JSON for complex data)
     */
    public function getValue(): string;

    /**
     * @return positive-int|null Bitrix24 user id for user-scoped settings, null for global/department settings
     */
    public function getBitrix24UserId(): ?int;

    /**
     * @return positive-int|null Bitrix24 department id for department-scoped settings, null for global/user settings
     */
    public function getBitrix24DepartmentId(): ?int;

    /**
     * @return positive-int|null Bitrix24 user id who last changed this setting
     */
    public function getChangedByBitrix24UserId(): ?int;

    /**
     * @return bool true if this setting is required for application operation
     */
    public function isRequired(): bool;

    /**
     * @return bool true if this setting is active (not soft-deleted)
     */
    public function isActive(): bool;

    /**
     * @return ApplicationSettingStatus setting status
     */
    public function getStatus(): ApplicationSettingStatus;

    /**
     * @return CarbonImmutable date and time setting created
     */
    public function getCreatedAt(): CarbonImmutable;

    /**
     * @return CarbonImmutable date and time setting last updated
     */
    public function getUpdatedAt(): CarbonImmutable;

    /**
     * Update setting value
     *
     * @param non-empty-string $value new setting value
     * @param positive-int|null $changedByBitrix24UserId user who changed the setting
     * @throws InvalidArgumentException
     */
    public function updateValue(string $value, ?int $changedByBitrix24UserId = null): void;

    /**
     * Mark setting as deleted (soft delete)
     *
     * @throws InvalidArgumentException
     */
    public function markAsDeleted(): void;

    /**
     * Check if this is a global setting (not user or department scoped)
     *
     * @return bool true if setting is global
     */
    public function isGlobal(): bool;

    /**
     * Check if this is a user-scoped setting
     *
     * @return bool true if setting is user-scoped
     */
    public function isPersonal(): bool;

    /**
     * Check if this is a department-scoped setting
     *
     * @return bool true if setting is department-scoped
     */
    public function isDepartmental(): bool;
}
