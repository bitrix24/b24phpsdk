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

namespace Bitrix24\SDK\Tests\Application\Contracts\Bitrix24Partners\Entity;

use Bitrix24\SDK\Application\Contracts\Bitrix24Partners\Entity\Bitrix24PartnerInterface;
use Bitrix24\SDK\Application\Contracts\Bitrix24Partners\Entity\Bitrix24PartnerStatus;
use Bitrix24\SDK\Application\Contracts\Bitrix24Partners\Events\Bitrix24PartnerExternalIdChangedEvent;
use Bitrix24\SDK\Application\Contracts\Events\AggregateRootEventsEmitterInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Tests\Builders\DemoDataGenerator;
use Carbon\CarbonImmutable;
use Generator;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Throwable;

#[CoversClass(Bitrix24PartnerInterface::class)]
abstract class Bitrix24PartnerInterfaceTest extends TestCase
{
    abstract protected function createBitrix24PartnerImplementation(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId
    ): Bitrix24PartnerInterface|AggregateRootEventsEmitterInterface;

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test getId method')]
    final public function testGetId(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $this->assertEquals($uuid, $bitrix24Partner->getId());
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test getCreatedAt method')]
    final public function testGetCreatedAt(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $this->assertEquals($createdAt, $bitrix24Partner->getCreatedAt());
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test getUpdatedAt method')]
    final public function testGetUpdatedAt(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $bitrix24Partner->changeTitle('new title');
        $this->assertNotEquals($updatedAt, $bitrix24Partner->getUpdatedAt());
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test changeExternalId method with empty string')]
    final public function testChangeExternalId(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);

        $newExternalId = Uuid::v7()->toRfc4122();
        $bitrix24Partner->changeExternalId($newExternalId);
        $this->assertEquals($newExternalId, $bitrix24Partner->getExternalId());
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test changeExternalId method with null')]
    final public function testChangeExternalIdWithNull(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $bitrix24Partner->changeExternalId(null);
        $this->assertNull($bitrix24Partner->getExternalId());
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test changeExternalId method')]
    final public function testChangeExternalIdWithEmptyString(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        $bitrix24Partner->changeExternalId('');
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test getStatus method')]
    final public function testGetStatus(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $this->assertEquals($bitrix24PartnerStatus, $bitrix24Partner->getStatus());
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test markAsActive method')]
    final public function testMarkAsActive(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);

        $blockComment = 'block partner';
        $bitrix24Partner->markAsBlocked($blockComment);
        $this->assertEquals(Bitrix24PartnerStatus::blocked, $bitrix24Partner->getStatus());
        $this->assertEquals($blockComment, $bitrix24Partner->getComment());

        $bitrix24Partner->markAsActive(null);
        $this->assertEquals(Bitrix24PartnerStatus::active, $bitrix24Partner->getStatus());
        $this->assertNull($bitrix24Partner->getComment());

        $this->expectException(InvalidArgumentException::class);
        $bitrix24Partner->markAsActive('mark as active');
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test markAsBlocked method')]
    final public function testMarkAsBlocked(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);

        $blockComment = 'block partner';
        $bitrix24Partner->markAsBlocked($blockComment);
        $this->assertEquals(Bitrix24PartnerStatus::blocked, $bitrix24Partner->getStatus());
        $this->assertEquals($blockComment, $bitrix24Partner->getComment());

        $this->expectException(InvalidArgumentException::class);
        $bitrix24Partner->markAsBlocked('mark as active');
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test markAsBlocked method')]
    final public function testMarkAsDeleted(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);

        $comment = 'delete partner';
        $bitrix24Partner->markAsDeleted($comment);
        $this->assertEquals(Bitrix24PartnerStatus::deleted, $bitrix24Partner->getStatus());
        $this->assertEquals($comment, $bitrix24Partner->getComment());

        $this->expectException(InvalidArgumentException::class);
        $bitrix24Partner->markAsBlocked('mark as deleted');
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test getTitle method')]
    final public function testGetTitle(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $this->assertEquals($title, $bitrix24Partner->getTitle());
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test changeTitle method')]
    final public function testChangeTitle(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $newTitle = 'new title';
        $bitrix24Partner->changeTitle($newTitle);
        $this->assertEquals($newTitle, $bitrix24Partner->getTitle());

        $newTitle = '';
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        $bitrix24Partner->changeTitle($newTitle);
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test getSite method')]
    final public function testGetSite(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $this->assertEquals($site, $bitrix24Partner->getSite());
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test changeSite method')]
    final public function testChangeSite(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $newSite = 'https://new-partner-site.com';
        $bitrix24Partner->changeSite($newSite);
        $this->assertEquals($newSite, $bitrix24Partner->getSite());

        $bitrix24Partner->changeSite(null);
        $this->assertNull($bitrix24Partner->getSite());

        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        $bitrix24Partner->changeSite('');
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test getPhone method')]
    final public function testGetPhone(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $this->assertEquals($phoneNumber, $bitrix24Partner->getPhone());
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test changePhone method')]
    final public function testChangePhone(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $newPhone = DemoDataGenerator::getMobilePhone();
        $bitrix24Partner->changePhone($newPhone);
        $this->assertEquals($newPhone, $bitrix24Partner->getPhone());

        $bitrix24Partner->changePhone(null);
        $this->assertNull($bitrix24Partner->getPhone());
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test getEmail method')]
    final public function testGetEmail(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $this->assertEquals($email, $bitrix24Partner->getEmail());
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test changeEmail method')]
    final public function testChangeEmail(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);

        $newEmail = DemoDataGenerator::getEmail();
        $bitrix24Partner->changeEmail($newEmail);
        $this->assertEquals($newEmail, $bitrix24Partner->getEmail());

        $bitrix24Partner->changeEmail(null);
        $this->assertNull($bitrix24Partner->getEmail());

        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        $bitrix24Partner->changeEmail('');
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test changeEmail method with invalid email')]
    final public function testChangeEmailWithInvalidEmail(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);

        $newEmail = '@partner.com';
        $this->expectException(InvalidArgumentException::class);
        $bitrix24Partner->changeEmail($newEmail);
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test getBitrix24PartnerNumber')]
    final public function testGetBitrix24PartnerNumber(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $this->assertEquals($bitrix24PartnerNumber, $bitrix24Partner->getBitrix24PartnerNumber());
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test getOpenLineId')]
    final public function testGetOpenLineId(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $this->assertEquals($openLineId, $bitrix24Partner->getOpenLineId());
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test changeOpenLineId')]
    final public function testChangeOpenLineId(
        Uuid                  $uuid,
        CarbonImmutable       $createdAt,
        CarbonImmutable       $updatedAt,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void
    {
        $bitrix24Partner = $this->createBitrix24PartnerImplementation($uuid, $createdAt, $updatedAt, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $newOpenLineId = Uuid::v7()->toRfc4122();
        $bitrix24Partner->changeOpenLineId($newOpenLineId);
        $this->assertEquals($newOpenLineId, $bitrix24Partner->getOpenLineId());

        $bitrix24Partner->changeOpenLineId(null);
        $this->assertNull($bitrix24Partner->getOpenLineId());

        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        $bitrix24Partner->changeOpenLineId('');
    }

    /**
     * @throws NumberParseException
     * @throws InvalidArgumentException
     */
    public static function bitrix24PartnerDataProvider(): Generator
    {
        yield 'partner-status-active-all-fields' => [
            Uuid::v7(), //id
            CarbonImmutable::now(), // createdAt
            CarbonImmutable::now(), // updatedAt
            Bitrix24PartnerStatus::active,
            'Bitrix24 Partner LLC', // title
            12345, // bitrix24 partner number, optional
            'https://bitrix24-partner.com', // site, optional
            DemoDataGenerator::getMobilePhone(), // phone, optional
            DemoDataGenerator::getEmail(), // email, optional
            'open-line-id', // open line id, optional
            Uuid::v7()->toRfc4122(), // externalId, optional
            'comment', // comment, optional
        ];
    }
}
