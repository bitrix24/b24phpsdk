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

namespace Bitrix24\SDK\Tests\Application\Contracts\ApplicationInstallations\Entity;

use Bitrix24\SDK\Application\ApplicationStatus;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationInterface;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationStatus;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Events\ApplicationInstallationMarkedNeedReinstallEvent;
use Bitrix24\SDK\Application\Contracts\Events\AggregateRootEventsEmitterInterface;
use Bitrix24\SDK\Application\PortalLicenseFamily;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\LogicException;
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

#[CoversClass(ApplicationInstallationInterface::class)]
abstract class ApplicationInstallationInterfaceTest extends TestCase
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

    /**
     * @param non-empty-string|null $comment
     */
    #[Test]
    #[DataProvider('needReinstallEventCommentDataProvider')]
    final public function testMarkAsNeedReinstallEmitsEvent(?string $comment): void
    {
        $previousTestNow = CarbonImmutable::getTestNow();
        try {
            CarbonImmutable::setTestNow('2026-01-01 10:00:00');
            $installation = $this->createInstallationForStaleTransition();
            $this->assertInstanceOf(AggregateRootEventsEmitterInterface::class, $installation);
            $installation->emitEvents();
            $id = $installation->getId();
            $accountId = $installation->getBitrix24AccountId();
            $createdAt = $installation->getCreatedAt();
            $updatedAt = $installation->getUpdatedAt();
            $transitionTime = CarbonImmutable::parse('2026-01-02 10:00:00');
            CarbonImmutable::setTestNow($transitionTime);

            $installation->markAsNeedReinstall($comment);

            $this->assertSame(ApplicationInstallationStatus::needReinstall, $installation->getStatus());
            $this->assertSame('needReinstall', $installation->getStatus()->value);
            $this->assertSame($comment, $installation->getComment());
            $this->assertEquals($id, $installation->getId());
            $this->assertEquals($accountId, $installation->getBitrix24AccountId());
            $this->assertEquals($createdAt, $installation->getCreatedAt());
            $this->assertTrue($installation->getUpdatedAt()->greaterThan($updatedAt));
            $this->assertEquals($transitionTime, $installation->getUpdatedAt());

            $events = $installation->emitEvents();
            $this->assertCount(1, $events);
            $event = array_values($events)[0];
            $this->assertInstanceOf(ApplicationInstallationMarkedNeedReinstallEvent::class, $event);
            $this->assertEquals($id, $event->applicationInstallationId);
            $this->assertEquals($installation->getUpdatedAt(), $event->timestamp);
            $this->assertSame($comment, $event->comment);
            $this->assertSame([], $installation->emitEvents());
        } finally {
            CarbonImmutable::setTestNow($previousTestNow);
        }
    }

    public static function needReinstallEventCommentDataProvider(): Generator
    {
        yield 'without comment' => [null];
        yield 'with comment' => ['Timed out waiting for ONAPPINSTALL'];
    }

    #[Test]
    #[DataProvider('rejectedNeedReinstallStatusDataProvider')]
    final public function testMarkAsNeedReinstallRejectsOtherStatusesWithoutMutation(string $status): void
    {
        $previousTestNow = CarbonImmutable::getTestNow();
        try {
            CarbonImmutable::setTestNow('2026-01-01 10:00:00');
            $installation = $this->createInstallationForStaleTransition();
            $this->assertInstanceOf(AggregateRootEventsEmitterInterface::class, $installation);
            if ($status === 'needReinstall') {
                $installation->markAsNeedReinstall('Original comment');
            } else {
                $installation->markAsBlocked('Original comment');
                if ($status === 'active') {
                    $installation->markAsActive('Original comment');
                } elseif ($status === 'deleted') {
                    $installation->applicationUninstalled(null);
                }
            }

            $installation->setApplicationToken('preserved-token');
            $installation->emitEvents();
            $before = $this->installationState($installation);
            CarbonImmutable::setTestNow('2026-01-02 10:00:00');
            try {
                $installation->markAsNeedReinstall('Rejected comment');
                $this->fail('Only new installations may require reinstallation.');
            } catch (LogicException) {
                $this->assertEquals($before, $this->installationState($installation));
                $this->assertTrue($installation->isApplicationTokenValid('preserved-token'));
                $this->assertSame([], $installation->emitEvents());
            }
        } finally {
            CarbonImmutable::setTestNow($previousTestNow);
        }
    }

    public static function rejectedNeedReinstallStatusDataProvider(): Generator
    {
        yield 'active' => ['active'];
        yield 'blocked' => ['blocked'];
        yield 'deleted' => ['deleted'];
        yield 'needReinstall' => ['needReinstall'];
    }

    #[Test]
    final public function testNeedReinstallCanBeUninstalledWithoutToken(): void
    {
        $previousTestNow = CarbonImmutable::getTestNow();
        try {
            CarbonImmutable::setTestNow('2026-01-01 10:00:00');
            $installation = $this->createInstallationForStaleTransition();
            $installation->markAsNeedReinstall(null);
            $id = $installation->getId();
            $createdAt = $installation->getCreatedAt();
            $updatedAt = $installation->getUpdatedAt();
            CarbonImmutable::setTestNow('2026-01-02 10:00:00');

            $installation->applicationUninstalled(null);

            $this->assertSame(ApplicationInstallationStatus::deleted, $installation->getStatus());
            $this->assertEquals($id, $installation->getId());
            $this->assertEquals($createdAt, $installation->getCreatedAt());
            $this->assertTrue($installation->getUpdatedAt()->greaterThan($updatedAt));
        } finally {
            CarbonImmutable::setTestNow($previousTestNow);
        }
    }

    #[Test]
    final public function testNeedReinstallCannotBeBlocked(): void
    {
        $installation = $this->createInstallationForStaleTransition();
        $this->assertInstanceOf(AggregateRootEventsEmitterInterface::class, $installation);
        $installation->markAsNeedReinstall('Original comment');
        $installation->emitEvents();
        $before = $this->installationState($installation);

        try {
            $installation->markAsBlocked('Rejected comment');
            $this->fail('Only new and active installations may be blocked.');
        } catch (LogicException) {
            $this->assertEquals($before, $this->installationState($installation));
            $this->assertSame([], $installation->emitEvents());
        }
    }

    private function createInstallationForStaleTransition(): ApplicationInstallationInterface
    {
        return $this->createApplicationInstallationImplementation(
            Uuid::v7(),
            ApplicationInstallationStatus::new,
            Uuid::v7(),
            ApplicationStatus::subscription(),
            PortalLicenseFamily::nfr,
            42,
            Uuid::v7(),
            Uuid::v7(),
            Uuid::v7(),
            'stale-installation-test'
        );
    }

    /**
     * @return array<mixed>
     */
    private function installationState(ApplicationInstallationInterface $installation): array
    {
        return [
            $installation->getId(),
            $installation->getStatus(),
            $installation->getComment(),
            $installation->getCreatedAt(),
            $installation->getUpdatedAt(),
            $installation->getBitrix24AccountId(),
            $installation->getApplicationStatus(),
            $installation->getPortalLicenseFamily(),
            $installation->getPortalUsersCount(),
            $installation->getContactPersonId(),
            $installation->getBitrix24PartnerContactPersonId(),
            $installation->getBitrix24PartnerId(),
            $installation->getExternalId(),
        ];
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test getId method')]
    final public function testGetId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $this->assertEquals($uuid, $installation->getId());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test testGetBitrix24AccountId method')]
    final public function testGetBitrix24AccountId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $this->assertEquals($bitrix24AccountUuid, $installation->getBitrix24AccountId());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test getContactPersonId method')]
    final public function testGetContactPersonId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $this->assertEquals($clientContactPersonUuid, $installation->getContactPersonId());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test bindContactPerson method')]
    final public function testBindContactPerson(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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

        $newContactPersonId = Uuid::v7();
        $installation->linkContactPerson($newContactPersonId);
        $this->assertEquals($newContactPersonId, $installation->getContactPersonId());
        $this->assertFalse($installation->getCreatedAt()->equalTo($installation->getUpdatedAt()));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test unbindContactPerson method')]
    final public function testUnbindContactPerson(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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

        $newContactPersonId = Uuid::v7();
        $installation->linkContactPerson($newContactPersonId);
        $this->assertEquals($newContactPersonId, $installation->getContactPersonId());
        $installation->unlinkContactPerson();
        $this->assertNull($installation->getContactPersonId());
        $this->assertFalse($installation->getCreatedAt()->equalTo($installation->getUpdatedAt()));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test getBitrix24PartnerContactPersonId method')]
    final public function testGetBitrix24PartnerContactPersonId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $this->assertEquals($partnerContactPersonUuid, $installation->getBitrix24PartnerContactPersonId());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test linkBitrix24PartnerContactPerson method')]
    final public function testLinkBitrix24PartnerContactPerson(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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

        $newBitrix24PartnerContactPersonId = Uuid::v7();
        $installation->linkBitrix24PartnerContactPerson($newBitrix24PartnerContactPersonId);
        $this->assertEquals($newBitrix24PartnerContactPersonId, $installation->getBitrix24PartnerContactPersonId());
        $this->assertFalse($installation->getCreatedAt()->equalTo($installation->getUpdatedAt()));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test unlinkBitrix24PartnerContactPerson method')]
    final public function testUnlinkBitrix24PartnerContactPerson(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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

        $newBitrix24PartnerContactPersonId = Uuid::v7();
        $installation->linkBitrix24PartnerContactPerson($newBitrix24PartnerContactPersonId);
        $this->assertEquals($newBitrix24PartnerContactPersonId, $installation->getBitrix24PartnerContactPersonId());
        $installation->unlinkBitrix24PartnerContactPerson();
        $this->assertNull($installation->getBitrix24PartnerContactPersonId());
        $this->assertFalse($installation->getCreatedAt()->equalTo($installation->getUpdatedAt()));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test linkBitrix24Partner method')]
    final public function linkBitrix24Partner(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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

        $newBitrix24PartnerUuid = Uuid::v7();
        $installation->linkBitrix24Partner($newBitrix24PartnerUuid);
        $this->assertEquals($newBitrix24PartnerUuid, $installation->getBitrix24PartnerId());
        $this->assertFalse($installation->getCreatedAt()->equalTo($installation->getUpdatedAt()));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test unlinkBitrix24Partner method')]
    final public function unlinkBitrix24Partner(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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

        $newBitrix24PartnerUuid = Uuid::v7();
        $installation->linkBitrix24Partner($newBitrix24PartnerUuid);
        $this->assertEquals($newBitrix24PartnerUuid, $installation->getBitrix24PartnerId());
        $installation->unlinkBitrix24Partner();
        $this->assertNull($installation->getBitrix24PartnerId());
        $this->assertFalse($installation->getCreatedAt()->equalTo($installation->getUpdatedAt()));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test getExternalId method')]
    final public function testGetExternalId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $this->assertEquals($externalId, $installation->getExternalId());
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test setExternalId method')]
    final public function testSetExternalId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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

        $newExternalId = Uuid::v7()->toRfc4122();
        $installation->setExternalId($newExternalId);
        $this->assertEquals($newExternalId, $installation->getExternalId());

        $installation->setExternalId(null);
        $this->assertNull($installation->getExternalId());

        $this->expectException(InvalidArgumentException::class);
        $installation->setExternalId('');
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test getStatus method')]
    final public function testGetStatus(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $this->assertEquals($applicationInstallationStatus, $installation->getStatus());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test applicationInstalled method')]
    final public function testApplicationInstalled(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $installation->applicationInstalled();
        $this->assertEquals(ApplicationInstallationStatus::active, $installation->getStatus());
        $this->assertFalse($installation->getCreatedAt()->equalTo($installation->getUpdatedAt()));

        // try to finish installation in wrong state
        $this->expectException(LogicException::class);
        $installation->applicationInstalled();
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test applicationUninstalled method')]
    final public function testApplicationUninstalled(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $installation->applicationInstalled();
        // a few moments later
        $installation->applicationUninstalled();
        $this->assertEquals(ApplicationInstallationStatus::deleted, $installation->getStatus());
        $this->assertFalse($installation->getCreatedAt()->equalTo($installation->getUpdatedAt()));

        // try to finish installation in wrong state
        $this->expectException(LogicException::class);
        $installation->applicationUninstalled();
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test markAsActive method')]
    final public function testMarkAsActive(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $installation->applicationInstalled();

        // a few moments later
        $installation->markAsBlocked('block installation');
        $this->assertEquals(ApplicationInstallationStatus::blocked, $installation->getStatus());

        // a few moments later
        $installation->markAsActive('activate installation');
        $this->assertEquals(ApplicationInstallationStatus::active, $installation->getStatus());


        // try to activate installation in wrong state
        $this->expectException(LogicException::class);
        $installation->markAsActive('activate installation in wrong state');
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test markAsBlocked method')]
    final public function testMarkAsBlocked(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $installation->applicationInstalled();

        // a few moments later
        $installation->markAsBlocked('block installation');
        $this->assertEquals(ApplicationInstallationStatus::blocked, $installation->getStatus());

        // try to activate installation in wrong state
        $this->expectException(LogicException::class);
        $installation->markAsBlocked('activate installation in wrong state');
    }

    #[Test]
    #[DataProvider('needReinstallCommentDataProvider')]
    final public function testMarkAsNeedReinstall(?string $comment): void
    {
        $previousTestNow = CarbonImmutable::getTestNow();

        try {
            $installation = $this->createInstallationForStatusTransition(ApplicationInstallationStatus::new);
            $id = $installation->getId();
            $accountId = $installation->getBitrix24AccountId();
            $createdAt = $installation->getCreatedAt();
            $updatedAt = $installation->getUpdatedAt();
            CarbonImmutable::setTestNow($updatedAt->addMinute());

            $installation->markAsNeedReinstall($comment);

            $this->assertSame(ApplicationInstallationStatus::needReinstall, $installation->getStatus());
            $this->assertSame($comment, $installation->getComment());
            $this->assertEquals($id, $installation->getId());
            $this->assertEquals($accountId, $installation->getBitrix24AccountId());
            $this->assertEquals($createdAt, $installation->getCreatedAt());
            $this->assertTrue($installation->getUpdatedAt()->greaterThan($updatedAt));
        } finally {
            CarbonImmutable::setTestNow($previousTestNow);
        }
    }

    public static function needReinstallCommentDataProvider(): Generator
    {
        yield 'without comment' => [null];
        yield 'with comment' => ['ONAPPINSTALL was not received before the installation TTL expired'];
    }

    #[Test]
    #[DataProvider('nonNewInstallationStatusDataProvider')]
    final public function testMarkAsNeedReinstallRejectsNonNewStatusWithoutMutation(string $status): void
    {
        $previousTestNow = CarbonImmutable::getTestNow();

        try {
            $installation = $this->createInstallationForStatusTransition(ApplicationInstallationStatus::new);
            if ($status === 'needReinstall') {
                $installation->markAsNeedReinstall('Original timeout comment');
            } else {
                $installation->markAsBlocked('Original block comment');
                if ($status === 'active') {
                    $installation->markAsActive('Original activation comment');
                } elseif ($status === 'deleted') {
                    $installation->applicationUninstalled();
                }
            }

            $initialStatus = $installation->getStatus();
            $this->assertSame($status, $initialStatus->value);
            $comment = $installation->getComment();
            $updatedAt = $installation->getUpdatedAt();
            CarbonImmutable::setTestNow($updatedAt->addMinute());

            try {
                $installation->markAsNeedReinstall('Must not replace the original comment');
                $this->fail('Only new installations can be marked as needing reinstallation.');
            } catch (LogicException) {
                $this->assertSame($initialStatus, $installation->getStatus());
                $this->assertSame($comment, $installation->getComment());
                $this->assertEquals($updatedAt, $installation->getUpdatedAt());
            }
        } finally {
            CarbonImmutable::setTestNow($previousTestNow);
        }
    }

    public static function nonNewInstallationStatusDataProvider(): Generator
    {
        yield 'active' => ['active'];
        yield 'blocked' => ['blocked'];
        yield 'deleted' => ['deleted'];
        yield 'needReinstall' => ['needReinstall'];
    }

    #[Test]
    final public function testApplicationUninstalledDirectlyFromNeedReinstall(): void
    {
        $installation = $this->createInstallationForStatusTransition(ApplicationInstallationStatus::new);
        $installation->markAsNeedReinstall('Installation TTL expired');

        $installation->applicationUninstalled();

        $this->assertSame(ApplicationInstallationStatus::deleted, $installation->getStatus());
    }

    #[Test]
    final public function testMarkAsBlockedRejectsNeedReinstallWithoutMutation(): void
    {
        $previousTestNow = CarbonImmutable::getTestNow();

        try {
            $installation = $this->createInstallationForStatusTransition(ApplicationInstallationStatus::new);
            $installation->markAsNeedReinstall('Installation TTL expired');
            $comment = $installation->getComment();
            $updatedAt = $installation->getUpdatedAt();
            CarbonImmutable::setTestNow($updatedAt->addMinute());

            try {
                $installation->markAsBlocked('Must not replace the timeout comment');
                $this->fail('Installations needing reinstallation cannot be blocked.');
            } catch (LogicException) {
                $this->assertSame(ApplicationInstallationStatus::needReinstall, $installation->getStatus());
                $this->assertSame($comment, $installation->getComment());
                $this->assertEquals($updatedAt, $installation->getUpdatedAt());
            }
        } finally {
            CarbonImmutable::setTestNow($previousTestNow);
        }
    }

    private function createInstallationForStatusTransition(ApplicationInstallationStatus $status): ApplicationInstallationInterface
    {
        return $this->createApplicationInstallationImplementation(
            Uuid::v7(),
            $status,
            Uuid::v7(),
            ApplicationStatus::subscription(),
            PortalLicenseFamily::nfr,
            null,
            null,
            null,
            null,
            null
        );
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test getApplicationStatus method')]
    final public function testGetApplicationStatus(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $this->assertEquals($applicationStatus, $installation->getApplicationStatus());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test changeApplicationStatus method')]
    final public function testChangeApplicationStatus(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $this->assertEquals($applicationStatus, $installation->getApplicationStatus());

        $newApplicationStatus = ApplicationStatus::trial();
        $installation->changeApplicationStatus($newApplicationStatus);
        $this->assertEquals($newApplicationStatus, $installation->getApplicationStatus());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test getPortalLicenseFamily method')]
    final public function testGetPortalLicenseFamily(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $this->assertEquals($portalLicenseFamily, $installation->getPortalLicenseFamily());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test changePortalLicenseFamily method')]
    final public function testChangePortalLicenseFamily(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $this->assertEquals($portalLicenseFamily, $installation->getPortalLicenseFamily());

        $newLicenseFamily = PortalLicenseFamily::ent;
        $installation->changePortalLicenseFamily($newLicenseFamily);
        $this->assertEquals($newLicenseFamily, $installation->getPortalLicenseFamily());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test getPortalUsersCount method')]
    final public function testGetPortalUsersCount(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $this->assertEquals($portalUsersCount, $installation->getPortalUsersCount());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test changePortalUsersCount method')]
    final public function testChangePortalUsersCount(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $this->assertEquals($portalUsersCount, $installation->getPortalUsersCount());

        $newUsersCount = 249;
        $installation->changePortalUsersCount($newUsersCount);
        $this->assertEquals($newUsersCount, $installation->getPortalUsersCount());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test getComment method')]
    final public function testGetComment(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $comment = 'test block';
        $installation->applicationInstalled();
        $installation->markAsBlocked($comment);
        $this->assertEquals($comment, $installation->getComment());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test getBitrix24PartnerId method')]
    final public function testGetBitrix24PartnerId(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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
        $this->assertEquals($partnerUuid, $installation->getBitrix24PartnerId());
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test isApplicationTokenValid method')]
    final public function testIsApplicationTokenValid(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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

        // First set a valid token
        $validToken = 'valid_application_token_' . uniqid('', true);
        $installation->setApplicationToken($validToken);

        // Test that the token is valid
        $this->assertTrue($installation->isApplicationTokenValid($validToken));

        // Test that an invalid token is not valid
        $this->assertFalse($installation->isApplicationTokenValid('invalid_token'));
    }

    #[Test]
    #[DataProvider('applicationInstallationDataProvider')]
    #[TestDox('test setApplicationToken method')]
    final public function testSetApplicationToken(
        Uuid $uuid,
        ApplicationInstallationStatus $applicationInstallationStatus,
        Uuid $bitrix24AccountUuid,
        ApplicationStatus $applicationStatus,
        PortalLicenseFamily $portalLicenseFamily,
        ?int $portalUsersCount,
        ?Uuid $clientContactPersonUuid,
        ?Uuid $partnerContactPersonUuid,
        ?Uuid $partnerUuid,
        ?string $externalId
    ): void {
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

        $applicationToken = 'application_token_' . uniqid('', true);
        $installation->setApplicationToken($applicationToken);

        // Verify the token is set correctly by checking if it's valid
        $this->assertTrue($installation->isApplicationTokenValid($applicationToken));

        // Test that empty token throws exception
        $this->expectException(InvalidArgumentException::class);
        $installation->setApplicationToken('');
    }

    public static function applicationInstallationDataProvider(): Generator
    {
        yield 'status-new-all-fields' => [
            Uuid::v7(), // uuid
            ApplicationInstallationStatus::new, // application installation status
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
