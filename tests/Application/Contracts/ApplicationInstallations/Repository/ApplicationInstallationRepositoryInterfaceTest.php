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

namespace Bitrix24\SDK\Tests\Application\Contracts\ApplicationInstallations\Repository;

use Bitrix24\SDK\Application\ApplicationStatus;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationInterface;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationStatus;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Exceptions\ApplicationInstallationNotFoundException;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Repository\ApplicationInstallationRepositoryInterface;
use Bitrix24\SDK\Application\PortalLicenseFamily;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Tests\Application\Contracts\TestRepositoryFlusherInterface;
use Carbon\CarbonImmutable;
use DateInterval;
use DateTime;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ApplicationInstallationRepositoryInterface::class)]
abstract class ApplicationInstallationRepositoryInterfaceTest extends TestCase
{
    abstract protected function createApplicationInstallationImplementation(
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
    ): ApplicationInstallationInterface;

    abstract protected function createApplicationInstallationRepositoryImplementation(): ApplicationInstallationRepositoryInterface;

    abstract protected function createRepositoryFlusherImplementation(): TestRepositoryFlusherInterface;

    /**
     * @throws ApplicationInstallationNotFoundException
     */
    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test save method for install start use case')]
    final public function testSave(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $installation = $this->createApplicationInstallationImplementation(
            $uuid,
            $applicationInstallationStatus,
            $bitrix24AccountUuid,
            $applicationStatus,
            $portalLicenseFamily,
            $portalUsersCount,
            $clientContactPersonUuid,
            $partnerContactPersonUuid,
            $partnerUuid,
            $externalId
        );
        $appInstallationRepo->save($installation);
        $flusher->flush();

        $this->assertEquals($installation, $appInstallationRepo->getById($installation->getId()));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test getById method for install start use case')]
    final public function testGetByIdHappyPath(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $installation = $this->createApplicationInstallationImplementation(
            $uuid,
            $applicationInstallationStatus,
            $bitrix24AccountUuid,
            $applicationStatus,
            $portalLicenseFamily,
            $portalUsersCount,
            $clientContactPersonUuid,
            $partnerContactPersonUuid,
            $partnerUuid,
            $externalId
        );
        $appInstallationRepo->save($installation);
        $flusher->flush();

        $this->assertEquals($installation, $appInstallationRepo->getById($installation->getId()));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test getById method for install start use case')]
    final public function testGetByIdWithNonExistsEntity(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();

        $this->expectException(ApplicationInstallationNotFoundException::class);
        $appInstallationRepo->getById(Uuid::v7());
    }

    /**
     * @throws ApplicationInstallationNotFoundException
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test delete method')]
    final public function testDeleteWithHappyPath(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $installation = $this->createApplicationInstallationImplementation(
            $uuid,
            $applicationInstallationStatus,
            $bitrix24AccountUuid,
            $applicationStatus,
            $portalLicenseFamily,
            $portalUsersCount,
            $clientContactPersonUuid,
            $partnerContactPersonUuid,
            $partnerUuid,
            $externalId
        );
        // successfully finish installation flow
        $installation->applicationInstalled();

        // few moments later application uninstalled
        // we receive ON_APPLICATION_UNINSTALL event and mark application installation as uninstalled: status = deleted
        $installation->applicationUninstalled();
        $appInstallationRepo->save($installation);
        $flusher->flush();

        // if we want we can delete application installation from repository
        $appInstallationRepo->delete($installation->getId());
        $flusher->flush();

        $this->expectException(ApplicationInstallationNotFoundException::class);
        $appInstallationRepo->getById($installation->getId());
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test delete method with unknown id')]
    final public function testDeleteWithUnknownId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();

        // try to delete unknown installation
        $this->expectException(ApplicationInstallationNotFoundException::class);
        $appInstallationRepo->delete(Uuid::v7());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test delete method with wrong state')]
    final public function testDeleteWithWrongState(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $installation = $this->createApplicationInstallationImplementation(
            $uuid,
            $applicationInstallationStatus,
            $bitrix24AccountUuid,
            $applicationStatus,
            $portalLicenseFamily,
            $portalUsersCount,
            $clientContactPersonUuid,
            $partnerContactPersonUuid,
            $partnerUuid,
            $externalId
        );
        $appInstallationRepo->save($installation);
        $flusher->flush();

        $this->expectException(InvalidArgumentException::class);
        $appInstallationRepo->delete($installation->getId());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test findByBitrix24AccountId method')]
    final public function testFindByBitrix24AccountId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $installation = $this->createApplicationInstallationImplementation(
            $uuid,
            $applicationInstallationStatus,
            $bitrix24AccountUuid,
            $applicationStatus,
            $portalLicenseFamily,
            $portalUsersCount,
            $clientContactPersonUuid,
            $partnerContactPersonUuid,
            $partnerUuid,
            $externalId
        );
        $appInstallationRepo->save($installation);
        $flusher->flush();

        $this->assertEquals($installation, $appInstallationRepo->findByBitrix24AccountId($bitrix24AccountUuid));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test findByBitrix24AccountId method with unknown id')]
    final public function testFindByBitrix24AccountIdWithUnknownId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();
        $this->assertNull($appInstallationRepo->findByBitrix24AccountId(Uuid::v7()));
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test findByExternalId method')]
    final public function testFindByExternalId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $installation = $this->createApplicationInstallationImplementation(
            $uuid,
            $applicationInstallationStatus,
            $bitrix24AccountUuid,
            $applicationStatus,
            $portalLicenseFamily,
            $portalUsersCount,
            $clientContactPersonUuid,
            $partnerContactPersonUuid,
            $partnerUuid,
            $externalId
        );
        $externalId = Uuid::v7()->toRfc4122();
        $installation->setExternalId($externalId);
        $appInstallationRepo->save($installation);
        $flusher->flush();

        $this->assertEquals([$installation], $appInstallationRepo->findByExternalId($externalId));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test findByExternalId method with unknown id')]
    final public function testFindByExternalIdWithUnknownId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();

        $externalId = Uuid::v7()->toRfc4122();
        $this->assertEquals([], $appInstallationRepo->findByExternalId($externalId));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test findByMemberId method')]
    final public function testFindByMemberId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $installation = $this->createApplicationInstallationImplementation(
            $uuid,
            $applicationInstallationStatus,
            $bitrix24AccountUuid,
            $applicationStatus,
            $portalLicenseFamily,
            $portalUsersCount,
            $clientContactPersonUuid,
            $partnerContactPersonUuid,
            $partnerUuid,
            $externalId
        );
        $appInstallationRepo->save($installation);
        $flusher->flush();

        $memberId = 'test-member-' . Uuid::v7()->toRfc4122();
        $result = $appInstallationRepo->findByBitrix24AccountMemberId($memberId);
        $this->assertTrue($result === null || $result instanceof ApplicationInstallationInterface);
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test findByMemberId method with unknown member id')]
    final public function testFindByMemberIdWithUnknownId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();

        $memberId = 'unknown-member-' . Uuid::v7()->toRfc4122();
        $this->assertNull($appInstallationRepo->findByBitrix24AccountMemberId($memberId));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test findByMemberId method with empty member id')]
    final public function testFindByMemberIdWithEmptyId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();

        $this->expectException(InvalidArgumentException::class);
        $appInstallationRepo->findByBitrix24AccountMemberId('');
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test findByApplicationToken method')]
    final public function testFindByApplicationToken(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $installation = $this->createApplicationInstallationImplementation(
            $uuid,
            $applicationInstallationStatus,
            $bitrix24AccountUuid,
            $applicationStatus,
            $portalLicenseFamily,
            $portalUsersCount,
            $clientContactPersonUuid,
            $partnerContactPersonUuid,
            $partnerUuid,
            $externalId
        );
        $applicationToken = 'test-token-' . Uuid::v7()->toRfc4122();
        $installation->setApplicationToken($applicationToken);
        $appInstallationRepo->save($installation);
        $flusher->flush();

        $this->assertEquals($installation, $appInstallationRepo->findByApplicationToken($applicationToken));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test findByApplicationToken method with unknown token')]
    final public function testFindByApplicationTokenWithUnknownToken(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();

        $applicationToken = 'unknown-token-' . Uuid::v7()->toRfc4122();
        $this->assertNull($appInstallationRepo->findByApplicationToken($applicationToken));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test findByApplicationToken method with empty token')]
    final public function testFindByApplicationTokenWithEmptyToken(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId,
    ): void {
        $appInstallationRepo = $this->createApplicationInstallationRepositoryImplementation();

        $this->expectException(InvalidArgumentException::class);
        $appInstallationRepo->findByApplicationToken('');
    }

    public static function applicationInstallationDataProvider(): Generator
    {
        yield 'status-new-all-fields' => [
            Uuid::v7(), // uuid
            ApplicationInstallationStatus::new, // application installation status
            CarbonImmutable::now(), // created at
            CarbonImmutable::createFromMutable((new DateTime())->add(new DateInterval('PT1H'))), // updated at
            Uuid::v7(), // bitrix24 account id
            ApplicationStatus::subscription(), // application status from bitrix24 api call response
            PortalLicenseFamily::nfr, // portal license family value
            42, // bitrix24 portal users count
            Uuid::v7(), // ?client contact person id
            Uuid::v7(), // ?partner contact person id
            Uuid::v7(), // ?partner id
            Uuid::v7()->toRfc4122(), // external id
        ];
        yield 'status-new-without-all-optional-fields' => [
            Uuid::v7(), // uuid
            ApplicationInstallationStatus::new, // application installation status
            CarbonImmutable::now(), // created at
            CarbonImmutable::createFromMutable((new DateTime())->add(new DateInterval('PT1H'))), // updated at
            Uuid::v7(), // bitrix24 account id
            ApplicationStatus::subscription(), // application status from bitrix24 api call response
            PortalLicenseFamily::nfr, // portal license family value
            null, // bitrix24 portal users count
            null, // ?client contact person id
            null, // ?partner contact person id
            null, // ?partner id
            null, // external id
        ];
    }
}