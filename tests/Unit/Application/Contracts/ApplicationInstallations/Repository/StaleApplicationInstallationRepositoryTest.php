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

use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationInterface;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationStatus;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Repository\ApplicationInstallationRepositoryInterface;
use Bitrix24\SDK\Tests\Unit\Application\Contracts\Bitrix24Accounts\Repository\InMemoryBitrix24AccountRepositoryImplementation;
use Carbon\CarbonImmutable;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ApplicationInstallationRepositoryInterface::class)]
#[CoversClass(InMemoryApplicationInstallationRepositoryImplementation::class)]
class StaleApplicationInstallationRepositoryTest extends TestCase
{
    #[Test]
    #[DataProvider('statusProvider')]
    public function testFindStaleInstallationsFiltersByStatusAndExclusiveCreatedAtThreshold(
        ApplicationInstallationStatus $status
    ): void {
        $repository = $this->createRepository();
        $olderThan = new CarbonImmutable('2026-09-01T12:00:00+00:00');
        $otherStatus = $status === ApplicationInstallationStatus::new
            ? ApplicationInstallationStatus::active
            : ApplicationInstallationStatus::new;
        $oldest = $this->createInstallation($status, $olderThan->subDays(2), $olderThan->addDay());
        $newer = $this->createInstallation($status, $olderThan->subDay(), $olderThan->addDays(2));
        $differentStatus = $this->createInstallation($otherStatus, $olderThan->subDays(3), $olderThan);
        $boundary = $this->createInstallation($status, $olderThan, $olderThan);
        $sameInstant = $this->createInstallation($status, $olderThan->setTimezone('Asia/Bishkek'), $olderThan);
        $recent = $this->createInstallation($status, $olderThan->addSecond(), $olderThan->addSecond());

        foreach ([$newer, $differentStatus, $boundary, $oldest, $sameInstant, $recent] as $installation) {
            $repository->save($installation);
        }

        $result = $repository->findStaleInstallations($status, $olderThan);

        self::assertSame([$oldest, $newer], $result);
        self::assertSame($newer, $repository->getById($newer->getId()));
        self::assertSame($recent, $repository->getById($recent->getId()));
    }

    #[Test]
    public function testFindStaleInstallationsReturnsEmptyArrayWhenNothingMatches(): void
    {
        $repository = $this->createRepository();
        $olderThan = new CarbonImmutable('2026-09-01T12:00:00+00:00');

        self::assertSame([], $repository->findStaleInstallations(ApplicationInstallationStatus::new, $olderThan));

        $repository->save($this->createInstallation(ApplicationInstallationStatus::active, $olderThan->subDay(), $olderThan));
        $repository->save($this->createInstallation(ApplicationInstallationStatus::new, $olderThan, $olderThan));

        self::assertSame([], $repository->findStaleInstallations(ApplicationInstallationStatus::new, $olderThan));
    }

    public static function statusProvider(): Generator
    {
        foreach (ApplicationInstallationStatus::cases() as $status) {
            yield $status->value => [$status];
        }
    }

    private function createRepository(): InMemoryApplicationInstallationRepositoryImplementation
    {
        return new InMemoryApplicationInstallationRepositoryImplementation(
            new InMemoryBitrix24AccountRepositoryImplementation(new NullLogger()),
            new NullLogger()
        );
    }

    private function createInstallation(
        ApplicationInstallationStatus $status,
        CarbonImmutable $createdAt,
        CarbonImmutable $updatedAt
    ): ApplicationInstallationInterface {
        $installation = $this->createStub(ApplicationInstallationInterface::class);
        $installation->method('getId')->willReturn(Uuid::v7());
        $installation->method('getStatus')->willReturn($status);
        $installation->method('getCreatedAt')->willReturn($createdAt);
        $installation->method('getUpdatedAt')->willReturn($updatedAt);

        return $installation;
    }
}
