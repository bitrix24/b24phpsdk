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

namespace Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Repository;

use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationInterface;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationStatus;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Exceptions\ApplicationInstallationNotFoundException;
use Bitrix24\SDK\Application\Contracts\Bitrix24Accounts;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;

interface ApplicationInstallationRepositoryInterface
{
    /**
     * Save application installation fact to persistence storage
     */
    public function save(ApplicationInstallationInterface $applicationInstallation): void;

    /**
     * Get application installation by id
     *
     *
     * @throws ApplicationInstallationNotFoundException
     */
    public function getById(Uuid $uuid): ApplicationInstallationInterface;

    /**
     * Get the installation selected by the application's current execution context.
     *
     * The implementation must supply the current installation context explicitly.
     *
     * @throws ApplicationInstallationNotFoundException When no installation is selected or the selected installation does not exist.
     */
    public function getCurrent(): ApplicationInstallationInterface;

    /**
     * Delete application installation from persistence storage
     *
     * @throws ApplicationInstallationNotFoundException
     * @throws InvalidArgumentException
     */
    public function delete(Uuid $uuid): void;

    /**
     * Find application installation by external id
     *
     * @param non-empty-string $externalId
     * @return ApplicationInstallationInterface[]
     * @throws InvalidArgumentException
     */
    public function findByExternalId(string $externalId): array;

    /**
     * Find installations with the given status AND createdAt strictly before olderThan.
     *
     * Return matches ordered by createdAt ascending. The threshold is exclusive and
     * applies to creation time, not updatedAt. Return an empty array if nothing matches.
     *
     * @return ApplicationInstallationInterface[]
     */
    public function findStaleInstallations(ApplicationInstallationStatus $status, CarbonImmutable $olderThan): array;

    /**
     * Find application installation by application token
     *
     * @param non-empty-string $applicationToken
     */
    public function findByApplicationToken(string $applicationToken): ?ApplicationInstallationInterface;

    /**
     * Find application installation by related Bitrix24 account member_id
     *
     * @param non-empty-string $memberId
     */
    public function findByBitrix24AccountMemberId(string $memberId): ?ApplicationInstallationInterface;

    /**
     * Find application installation by bitrix24 account id
     **/
    public function findByBitrix24AccountId(Uuid $uuid): ?ApplicationInstallationInterface;
}