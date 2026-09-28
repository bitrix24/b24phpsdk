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

namespace Bitrix24\SDK\Tests\Application\Contracts\Bitrix24Partners\Repository;

use Bitrix24\SDK\Application\Contracts\Bitrix24Partners\Entity\Bitrix24PartnerInterface;
use Bitrix24\SDK\Application\Contracts\Bitrix24Partners\Entity\Bitrix24PartnerStatus;
use Bitrix24\SDK\Application\Contracts\Bitrix24Partners\Exceptions\Bitrix24PartnerNotFoundException;
use Bitrix24\SDK\Application\Contracts\Bitrix24Partners\Repository\Bitrix24PartnerRepositoryInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Tests\Application\Contracts\TestRepositoryFlusherInterface;
use Bitrix24\SDK\Tests\Builders\DemoDataGenerator;
use Generator;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Bitrix24PartnerInterface::class)]
abstract class Bitrix24PartnerRepositoryInterfaceTest extends TestCase
{
    abstract protected function createBitrix24PartnerImplementation(
        Uuid                  $uuid,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        ?int                  $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId
    ): Bitrix24PartnerInterface;

    abstract protected function createBitrix24PartnerRepositoryImplementation(): Bitrix24PartnerRepositoryInterface;

    abstract protected function createRepositoryFlusherImplementation(): TestRepositoryFlusherInterface;

    /**
     * @throws InvalidArgumentException
     * @throws Bitrix24PartnerNotFoundException
     */
    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test save method')]
    final public function testSave(
        Uuid                  $uuid,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        ?int                  $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void {
        $b24Partner = $this->createBitrix24PartnerImplementation($uuid, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $b24PartnerRepository->save($b24Partner);
        $flusher->flush();

        $res = $b24PartnerRepository->getById($b24Partner->getId());
        $this->assertEquals($b24Partner, $res);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Bitrix24PartnerNotFoundException
     */
    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test save method')]
    final public function testGetById(
        Uuid                  $uuid,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        ?int                  $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void {
        $b24Partner = $this->createBitrix24PartnerImplementation($uuid, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $b24PartnerRepository->save($b24Partner);
        $flusher->flush();

        $res = $b24PartnerRepository->getById($b24Partner->getId());
        $this->assertEquals($b24Partner, $res);

        $this->expectException(Bitrix24PartnerNotFoundException::class);
        $b24PartnerRepository->getById(Uuid::v7());
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test findByBitrix24PartnerNumber method')]
    final public function testFindByBitrix24PartnerNumber(
        Uuid                  $uuid,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        ?int                  $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void {
        $b24Partner = $this->createBitrix24PartnerImplementation($uuid, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $b24PartnerRepository->save($b24Partner);
        $flusher->flush();

        $res = $b24PartnerRepository->findByBitrix24PartnerNumber($b24Partner->getBitrix24PartnerNumber());
        $this->assertEquals($b24Partner, $res);

        $this->assertNull($b24PartnerRepository->findByBitrix24PartnerNumber(0));
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[TestDox('findByBitrix24PartnerNumber ignores deleted partners by default')]
    final public function testFindByBitrix24PartnerNumberIgnoresDeletedByDefault(): void
    {
        $b24Partner = $this->createBitrix24PartnerImplementation(
            Uuid::v7(),
            Bitrix24PartnerStatus::deleted,
            'Deleted Bitrix24 Partner LLC',
            16592200,
            null,
            null,
            null,
            null,
            null
        );
        $b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $b24PartnerRepository->save($b24Partner);
        $flusher->flush();

        $this->assertNull($b24PartnerRepository->findByBitrix24PartnerNumber($b24Partner->getBitrix24PartnerNumber()));
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[TestDox('findByBitrix24PartnerNumber can include deleted partners')]
    final public function testFindByBitrix24PartnerNumberCanIncludeDeleted(): void
    {
        $b24Partner = $this->createBitrix24PartnerImplementation(
            Uuid::v7(),
            Bitrix24PartnerStatus::deleted,
            'Deleted Bitrix24 Partner LLC',
            16592200,
            null,
            null,
            null,
            null,
            null
        );
        $b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $b24PartnerRepository->save($b24Partner);
        $flusher->flush();

        $this->assertEquals(
            $b24Partner,
            $b24PartnerRepository->findByBitrix24PartnerNumber(
                $b24Partner->getBitrix24PartnerNumber(),
                withDeleted: true
            )
        );
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test findByTitle method')]
    final public function testFindByTitle(
        Uuid                  $uuid,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        ?int                  $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void {
        $b24Partner = $this->createBitrix24PartnerImplementation($uuid, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $b24PartnerRepository->save($b24Partner);
        $flusher->flush();

        $res = $b24PartnerRepository->findByTitle($b24Partner->getTitle());
        $this->assertEquals($b24Partner, $res[0]);

        $this->assertEmpty($b24PartnerRepository->findByTitle('test'));
    }

    #[Test]
    #[DataProvider('bitrix24PartnerDataProvider')]
    #[TestDox('test findByExternalId method')]
    final public function testFindByExternalId(
        Uuid                  $uuid,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        ?int                  $bitrix24PartnerNumber,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        string                $comment
    ): void {
        $b24Partner = $this->createBitrix24PartnerImplementation($uuid, $bitrix24PartnerStatus, $title, $bitrix24PartnerNumber, $site, $phoneNumber, $email, $openLineId, $externalId);
        $b24PartnerRepository = $this->createBitrix24PartnerRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $b24PartnerRepository->save($b24Partner);
        $flusher->flush();

        $res = $b24PartnerRepository->findByExternalId($b24Partner->getExternalId());
        $this->assertEquals($b24Partner, $res[0]);

        $this->assertEmpty($b24PartnerRepository->findByExternalId('test'));
    }

    /**
     * @throws NumberParseException
     * @throws InvalidArgumentException
     */
    public static function bitrix24PartnerDataProvider(): Generator
    {
        yield 'partner-status-active-all-fields' => [
            Uuid::v7(), //id
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
