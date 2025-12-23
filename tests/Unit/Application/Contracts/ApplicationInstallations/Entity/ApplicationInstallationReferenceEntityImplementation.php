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

namespace Bitrix24\SDK\Tests\Unit\Application\Contracts\ApplicationInstallations\Entity;

use Bitrix24\SDK\Application\ApplicationStatus;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationInterface;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationStatus;
use Bitrix24\SDK\Application\PortalLicenseFamily;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\LogicException;
use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * Class ApplicationInstallationReferenceEntityImplementation
 *
 * This class uses ONLY for demonstration and tests interface, use cases for work with ApplicationInstallationInterface methods
 *
 */
final class ApplicationInstallationReferenceEntityImplementation implements ApplicationInstallationInterface
{
    private ?string $comment = null;

    private ?string $applicationToken = null;

    private readonly CarbonImmutable $createdAt;

    private CarbonImmutable $updatedAt;

    public function __construct(
        private readonly Uuid $id,
        private ApplicationInstallationStatus $applicationInstallationStatus,
        private readonly Uuid $bitrix24AccountUuid,
        private ApplicationStatus $applicationStatus,
        private PortalLicenseFamily $portalLicenseFamily,
        private ?int $portalUsersCount,
        private ?Uuid $clientContactPersonUuid,
        private ?Uuid $partnerContactPersonUuid,
        private ?Uuid $bitrix24PartnerUuid,
        private ?string $externalId,
    ) {
        $this->createdAt = new CarbonImmutable();
        $this->updatedAt = new CarbonImmutable();
    }

    #[\Override]
    public function getId(): Uuid
    {
        return $this->id;
    }

    #[\Override]
    public function getCreatedAt(): CarbonImmutable
    {
        return $this->createdAt;
    }

    #[\Override]
    public function getUpdatedAt(): CarbonImmutable
    {
        return $this->updatedAt;
    }

    #[\Override]
    public function getStatus(): ApplicationInstallationStatus
    {
        return $this->applicationInstallationStatus;
    }

    #[\Override]
    public function getBitrix24AccountId(): Uuid
    {
        return $this->bitrix24AccountUuid;
    }

    #[\Override]
    public function getApplicationStatus(): ApplicationStatus
    {
        return $this->applicationStatus;
    }

    #[\Override]
    public function getPortalLicenseFamily(): PortalLicenseFamily
    {
        return $this->portalLicenseFamily;
    }

    #[\Override]
    public function changePortalLicenseFamily(PortalLicenseFamily $portalLicenseFamily): void
    {
        $this->portalLicenseFamily = $portalLicenseFamily;
        $this->updatedAt = new CarbonImmutable();
    }

    #[\Override]
    public function getPortalUsersCount(): ?int
    {
        return $this->portalUsersCount;
    }

    #[\Override]
    public function changePortalUsersCount(int $usersCount): void
    {
        $this->portalUsersCount = $usersCount;
        $this->updatedAt = new CarbonImmutable();
    }

    #[\Override]
    public function getContactPersonId(): ?Uuid
    {
        return $this->clientContactPersonUuid;
    }

    #[\Override]
    public function getBitrix24PartnerContactPersonId(): ?Uuid
    {
        return $this->partnerContactPersonUuid;
    }

    #[\Override]
    public function linkBitrix24PartnerContactPerson(?Uuid $uuid): void
    {
        $this->partnerContactPersonUuid = $uuid;
        $this->updatedAt = new CarbonImmutable();
    }

    #[\Override]
    public function unlinkBitrix24PartnerContactPerson(): void
    {
        $this->partnerContactPersonUuid = null;
        $this->updatedAt = new CarbonImmutable();
    }

    #[\Override]
    public function getBitrix24PartnerId(): ?Uuid
    {
        return $this->bitrix24PartnerUuid;
    }

    #[\Override]
    public function linkBitrix24Partner(Uuid $uuid): void
    {
        $this->bitrix24PartnerUuid = $uuid;
        $this->updatedAt = new CarbonImmutable();
    }

    #[\Override]
    public function unlinkBitrix24Partner(): void
    {
        $this->bitrix24PartnerUuid = null;
        $this->updatedAt = new CarbonImmutable();
    }

    #[\Override]
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    #[\Override]
    public function setExternalId(?string $externalId): void
    {
        if (($externalId !== null) && trim($externalId) === '') {
            throw new InvalidArgumentException('externalId cannot be empty string');
        }

        $this->externalId = $externalId;
        $this->updatedAt = new CarbonImmutable();
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    public function applicationInstalled(?string $applicationToken = null): void
    {
        if ($this->applicationInstallationStatus !== ApplicationInstallationStatus::new) {
            throw new LogicException(
                sprintf(
                    'application installation must be in status «%s», current state «%s»',
                    ApplicationInstallationStatus::new->name,
                    $this->applicationInstallationStatus->name
                )
            );
        }

        if ($applicationToken !== null) {
            $this->setApplicationToken($applicationToken);
        }

        $this->applicationInstallationStatus = ApplicationInstallationStatus::active;
        $this->updatedAt = new CarbonImmutable();
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    public function applicationUninstalled(?string $applicationToken = null): void
    {
        if ($this->applicationInstallationStatus === ApplicationInstallationStatus::new || $this->applicationInstallationStatus === ApplicationInstallationStatus::deleted) {
            throw new LogicException(
                sprintf(
                    'application installation must be in status «%s» or «%s», current state «%s»',
                    ApplicationInstallationStatus::active->name,
                    ApplicationInstallationStatus::blocked->name,
                    $this->applicationInstallationStatus->name
                )
            );
        }

        if ($applicationToken !== null) {
            $this->setApplicationToken($applicationToken);
        }

        $this->applicationInstallationStatus = ApplicationInstallationStatus::deleted;
        $this->updatedAt = new CarbonImmutable();
    }

    #[\Override]
    public function markAsActive(?string $comment): void
    {
        if ($this->applicationInstallationStatus !== ApplicationInstallationStatus::blocked) {
            throw new LogicException(
                sprintf(
                    'you can activate application install only in state «%s», current state «%s»',
                    ApplicationInstallationStatus::blocked->name,
                    $this->applicationInstallationStatus->name
                )
            );
        }

        $this->applicationInstallationStatus = ApplicationInstallationStatus::active;
        $this->comment = $comment;
        $this->updatedAt = new CarbonImmutable();
    }

    #[\Override]
    public function markAsBlocked(?string $comment): void
    {
        if ($this->applicationInstallationStatus === ApplicationInstallationStatus::blocked || $this->applicationInstallationStatus === ApplicationInstallationStatus::deleted) {
            throw new LogicException(
                sprintf(
                    'you can block application install only in state «%s» or «%s», current state «%s»',
                    ApplicationInstallationStatus::new->name,
                    ApplicationInstallationStatus::active->name,
                    $this->applicationInstallationStatus->name
                )
            );
        }

        $this->applicationInstallationStatus = ApplicationInstallationStatus::blocked;
        $this->comment = $comment;
        $this->updatedAt = new CarbonImmutable();
    }

    #[\Override]
    public function changeApplicationStatus(ApplicationStatus $applicationStatus): void
    {
        $this->applicationStatus = $applicationStatus;
        $this->updatedAt = new CarbonImmutable();
    }

    #[\Override]
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param non-empty-string $applicationToken
     * @throws InvalidArgumentException
     */
    #[\Override]
    public function setApplicationToken(string $applicationToken): void
    {
        if (trim($applicationToken) === '') {
            throw new InvalidArgumentException('applicationToken cannot be empty string');
        }

        $this->applicationToken = $applicationToken;
        $this->updatedAt = new CarbonImmutable();
    }

    /**
     * @param non-empty-string $applicationToken
     */
    #[\Override]
    public function isApplicationTokenValid(string $applicationToken): bool
    {
        if ($this->applicationToken === null) {
            return false;
        }

        return $this->applicationToken === $applicationToken;
    }

    #[\Override]
    public function linkContactPerson(Uuid $uuid): void
    {
        $this->clientContactPersonUuid = $uuid;
        $this->updatedAt = new CarbonImmutable();
    }

    #[\Override]
    public function unlinkContactPerson(): void
    {
        $this->clientContactPersonUuid = null;
        $this->updatedAt = new CarbonImmutable();
    }
}
