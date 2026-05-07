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

namespace Bitrix24\SDK\Tests\Unit\Application\Contracts\ApplicationInstallations\Repository;

use Bitrix24\SDK\Application\ApplicationStatus;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationInterface;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationStatus;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Repository\ApplicationInstallationRepositoryInterface;
use Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Entity\Bitrix24AccountInterface;
use Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Entity\Bitrix24AccountStatus;
use Bitrix24\SDK\Application\PortalLicenseFamily;
use Bitrix24\SDK\Core\Credentials\AuthToken;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Tests\Application\Contracts\ApplicationInstallations\Repository\ApplicationInstallationRepositoryInterfaceTest;
use Bitrix24\SDK\Tests\Application\Contracts\NullableFlusher;
use Bitrix24\SDK\Tests\Application\Contracts\TestRepositoryFlusherInterface;
use Bitrix24\SDK\Tests\Unit\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationReferenceEntityImplementation;
use Bitrix24\SDK\Tests\Unit\Application\Contracts\Bitrix24Accounts\Entity\Bitrix24AccountReferenceEntityImplementation;
use Bitrix24\SDK\Tests\Unit\Application\Contracts\Bitrix24Accounts\Repository\InMemoryBitrix24AccountRepositoryImplementation;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Psr\Log\NullLogger;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ApplicationInstallationRepositoryInterface::class)]
class InMemoryApplicationInstallationRepositoryImplementationTest extends ApplicationInstallationRepositoryInterfaceTest
{
    private ?InMemoryBitrix24AccountRepositoryImplementation $bitrix24AccountRepository = null;

    #[\Override]
    protected function createApplicationInstallationImplementation(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): ApplicationInstallationInterface {
        return new ApplicationInstallationReferenceEntityImplementation(
            $uuid,
            $applicationInstallationStatus,
            $bitrix24AccountUuid,
            $applicationStatus,
            $portalLicenseFamily,
            $portalUsersCount,
            $clientContactPersonUuid,
            $partnerContactPersonUuid,
            $partnerUuid,
            $externalId,
        );
    }

    #[\Override]
    protected function createRepositoryFlusherImplementation(): TestRepositoryFlusherInterface
    {
        return new NullableFlusher();
    }

    #[\Override]
    protected function createApplicationInstallationRepositoryImplementation(): ApplicationInstallationRepositoryInterface
    {
        $this->bitrix24AccountRepository = new InMemoryBitrix24AccountRepositoryImplementation(new NullLogger());

        return new InMemoryApplicationInstallationRepositoryImplementation(
            $this->bitrix24AccountRepository,
            new NullLogger()
        );
    }

    #[Test]
    #[TestDox('findByMemberId returns installation for pending new master account')]
    public function testFindByMemberIdReturnsInstallationForNewMasterAccount(): void
    {
        $applicationInstallationRepository = $this->createApplicationInstallationRepositoryImplementation();
        $inMemoryBitrix24AccountRepositoryImplementation = $this->getBitrix24AccountRepository();

        $memberId = 'test-member-' . Uuid::v7()->toRfc4122();
        $bitrix24Account = $this->createBitrix24Account($memberId, Bitrix24AccountStatus::new);
        $applicationInstallation = $this->createApplicationInstallationForAccount($bitrix24Account->getId(), ApplicationInstallationStatus::new);

        $inMemoryBitrix24AccountRepositoryImplementation->save($bitrix24Account);
        $applicationInstallationRepository->save($applicationInstallation);

        $this->assertSame($applicationInstallation, $applicationInstallationRepository->findByBitrix24AccountMemberId($memberId));
    }

    #[Test]
    #[TestDox('findByMemberId returns installation for active master account')]
    public function testFindByMemberIdReturnsInstallationForActiveMasterAccount(): void
    {
        $applicationInstallationRepository = $this->createApplicationInstallationRepositoryImplementation();
        $inMemoryBitrix24AccountRepositoryImplementation = $this->getBitrix24AccountRepository();

        $memberId = 'test-member-' . Uuid::v7()->toRfc4122();
        $bitrix24Account = $this->createBitrix24Account($memberId, Bitrix24AccountStatus::active);
        $applicationInstallation = $this->createApplicationInstallationForAccount($bitrix24Account->getId(), ApplicationInstallationStatus::active);

        $inMemoryBitrix24AccountRepositoryImplementation->save($bitrix24Account);
        $applicationInstallationRepository->save($applicationInstallation);

        $this->assertSame($applicationInstallation, $applicationInstallationRepository->findByBitrix24AccountMemberId($memberId));
    }

    #[Test]
    #[TestDox('findByMemberId ignores deleted master accounts')]
    public function testFindByMemberIdIgnoresDeletedMasterAccounts(): void
    {
        $applicationInstallationRepository = $this->createApplicationInstallationRepositoryImplementation();
        $inMemoryBitrix24AccountRepositoryImplementation = $this->getBitrix24AccountRepository();

        $memberId = 'test-member-' . Uuid::v7()->toRfc4122();
        $bitrix24Account = $this->createBitrix24Account($memberId, Bitrix24AccountStatus::deleted);
        $applicationInstallation = $this->createApplicationInstallationForAccount($bitrix24Account->getId(), ApplicationInstallationStatus::active);

        $inMemoryBitrix24AccountRepositoryImplementation->save($bitrix24Account);
        $applicationInstallationRepository->save($applicationInstallation);

        $this->assertNull($applicationInstallationRepository->findByBitrix24AccountMemberId($memberId));
    }

    #[Test]
    #[TestDox('findByMemberId skips deleted installation and falls back to new account installation')]
    public function testFindByMemberIdFallsBackWhenActiveInstallationDeleted(): void
    {
        $applicationInstallationRepository = $this->createApplicationInstallationRepositoryImplementation();
        $inMemoryBitrix24AccountRepositoryImplementation = $this->getBitrix24AccountRepository();

        $memberId = 'test-member-' . Uuid::v7()->toRfc4122();
        $bitrix24Account = $this->createBitrix24Account($memberId, Bitrix24AccountStatus::active);
        $newAccount = $this->createBitrix24Account($memberId, Bitrix24AccountStatus::new);
        $applicationInstallation = $this->createApplicationInstallationForAccount($bitrix24Account->getId(), ApplicationInstallationStatus::deleted);
        $newInstallation = $this->createApplicationInstallationForAccount($newAccount->getId(), ApplicationInstallationStatus::new);

        $inMemoryBitrix24AccountRepositoryImplementation->save($bitrix24Account);
        $inMemoryBitrix24AccountRepositoryImplementation->save($newAccount);

        $applicationInstallationRepository->save($applicationInstallation);
        $applicationInstallationRepository->save($newInstallation);

        $this->assertSame($newInstallation, $applicationInstallationRepository->findByBitrix24AccountMemberId($memberId));
    }

    #[Test]
    #[TestDox('findByMemberId prefers active installation over new installation')]
    public function testFindByMemberIdPrefersActiveInstallationOverNewInstallation(): void
    {
        $applicationInstallationRepository = $this->createApplicationInstallationRepositoryImplementation();
        $inMemoryBitrix24AccountRepositoryImplementation = $this->getBitrix24AccountRepository();

        $memberId = 'test-member-' . Uuid::v7()->toRfc4122();
        $bitrix24Account = $this->createBitrix24Account($memberId, Bitrix24AccountStatus::new);
        $activeAccount = $this->createBitrix24Account($memberId, Bitrix24AccountStatus::active);
        $applicationInstallation = $this->createApplicationInstallationForAccount($bitrix24Account->getId(), ApplicationInstallationStatus::new);
        $activeInstallation = $this->createApplicationInstallationForAccount($activeAccount->getId(), ApplicationInstallationStatus::active);

        $inMemoryBitrix24AccountRepositoryImplementation->save($bitrix24Account);
        $inMemoryBitrix24AccountRepositoryImplementation->save($activeAccount);

        $applicationInstallationRepository->save($applicationInstallation);
        $applicationInstallationRepository->save($activeInstallation);

        $this->assertSame($activeInstallation, $applicationInstallationRepository->findByBitrix24AccountMemberId($memberId));
    }

    #[Test]
    #[TestDox('findByMemberId returns blocked installation when active and new are unavailable')]
    public function testFindByMemberIdReturnsBlockedInstallationWhenNeeded(): void
    {
        $applicationInstallationRepository = $this->createApplicationInstallationRepositoryImplementation();
        $inMemoryBitrix24AccountRepositoryImplementation = $this->getBitrix24AccountRepository();

        $memberId = 'test-member-' . Uuid::v7()->toRfc4122();
        $bitrix24Account = $this->createBitrix24Account($memberId, Bitrix24AccountStatus::blocked);
        $applicationInstallation = $this->createApplicationInstallationForAccount($bitrix24Account->getId(), ApplicationInstallationStatus::blocked);

        $inMemoryBitrix24AccountRepositoryImplementation->save($bitrix24Account);
        $applicationInstallationRepository->save($applicationInstallation);

        $this->assertSame($applicationInstallation, $applicationInstallationRepository->findByBitrix24AccountMemberId($memberId));
    }

    private function getBitrix24AccountRepository(): InMemoryBitrix24AccountRepositoryImplementation
    {
        if (!$this->bitrix24AccountRepository instanceof InMemoryBitrix24AccountRepositoryImplementation) {
            self::fail('Bitrix24 account repository is not initialized');
        }

        return $this->bitrix24AccountRepository;
    }

    private function createBitrix24Account(string $memberId, Bitrix24AccountStatus $bitrix24AccountStatus): Bitrix24AccountInterface
    {
        $bitrix24AccountReferenceEntityImplementation = new Bitrix24AccountReferenceEntityImplementation(
            Uuid::v7(),
            random_int(1, 100_000),
            true,
            true,
            $memberId,
            sprintf('https://example-%s.com', Uuid::v7()->toRfc4122()),
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope(['crm', 'task'])
        );

        return match ($bitrix24AccountStatus) {
            Bitrix24AccountStatus::new => $bitrix24AccountReferenceEntityImplementation,
            Bitrix24AccountStatus::active => $this->markAccountAsActive($bitrix24AccountReferenceEntityImplementation),
            Bitrix24AccountStatus::blocked => $this->markAccountAsBlocked($bitrix24AccountReferenceEntityImplementation),
            Bitrix24AccountStatus::deleted => $this->markAccountAsDeleted($bitrix24AccountReferenceEntityImplementation),
        };
    }

    private function createApplicationInstallationForAccount(
        Uuid $bitrix24AccountUuid,
        ApplicationInstallationStatus $applicationInstallationStatus
    ): ApplicationInstallationInterface {
        return new ApplicationInstallationReferenceEntityImplementation(
            Uuid::v7(),
            $applicationInstallationStatus,
            $bitrix24AccountUuid,
            ApplicationStatus::subscription(),
            PortalLicenseFamily::nfr,
            42,
            null,
            null,
            null,
            null,
        );
    }

    private function markAccountAsActive(Bitrix24AccountInterface $bitrix24Account): Bitrix24AccountInterface
    {
        $bitrix24Account->applicationInstalled('application_token');

        return $bitrix24Account;
    }

    private function markAccountAsBlocked(Bitrix24AccountInterface $bitrix24Account): Bitrix24AccountInterface
    {
        $bitrix24Account->markAsBlocked('block account for test');

        return $bitrix24Account;
    }

    private function markAccountAsDeleted(Bitrix24AccountInterface $bitrix24Account): Bitrix24AccountInterface
    {
        $bitrix24Account->applicationInstalled('application_token');
        $bitrix24Account->applicationUninstalled('application_token');

        return $bitrix24Account;
    }
}
