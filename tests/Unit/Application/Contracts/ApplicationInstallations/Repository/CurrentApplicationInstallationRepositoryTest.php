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
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Exceptions\ApplicationInstallationNotFoundException;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Repository\ApplicationInstallationRepositoryInterface;
use Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Repository\Bitrix24AccountRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ApplicationInstallationRepositoryInterface::class)]
#[CoversClass(InMemoryApplicationInstallationRepositoryImplementation::class)]
class CurrentApplicationInstallationRepositoryTest extends TestCase
{
    public function testGetCurrentReturnsSelectedInstallationAmongSeveral(): void
    {
        $id = Uuid::v7();
        $repository = $this->createRepository($id);
        $current = $this->createInstallation($id);
        $repository->save($this->createInstallation(Uuid::v7()));
        $repository->save($current);
        $repository->save($this->createInstallation(Uuid::v7()));

        $this->assertSame($current, $repository->getCurrent());
    }

    public function testGetCurrentWithoutContextThrowsEvenWhenStorageIsNotEmpty(): void
    {
        $repository = $this->createRepository(null);
        $repository->save($this->createInstallation(Uuid::v7()));

        $this->expectException(ApplicationInstallationNotFoundException::class);
        $repository->getCurrent();
    }

    public function testGetCurrentWithUnknownSelectedIdThrows(): void
    {
        $repository = $this->createRepository(Uuid::v7());
        $repository->save($this->createInstallation(Uuid::v7()));

        $this->expectException(ApplicationInstallationNotFoundException::class);
        $repository->getCurrent();
    }

    public function testGetCurrentReturnsLatestStoredVersionOfSelectedInstallation(): void
    {
        $id = Uuid::v7();
        $repository = $this->createRepository($id);
        $repository->save($this->createInstallation($id));

        $replacement = $this->createInstallation($id);
        $repository->save($replacement);

        $this->assertSame($replacement, $repository->getCurrent());
    }

    public function testGetCurrentAfterSelectedInstallationIsRemovedThrows(): void
    {
        $id = Uuid::v7();
        $repository = $this->createRepository($id);
        $repository->save($this->createInstallation($id, ApplicationInstallationStatus::deleted));
        $repository->save($this->createInstallation(Uuid::v7()));
        $repository->delete($id);

        $this->expectException(ApplicationInstallationNotFoundException::class);
        $repository->getCurrent();
    }

    private function createRepository(?Uuid $currentInstallationId): ApplicationInstallationRepositoryInterface
    {
        return new InMemoryApplicationInstallationRepositoryImplementation(
            $this->createStub(Bitrix24AccountRepositoryInterface::class),
            new NullLogger(),
            $currentInstallationId
        );
    }

    private function createInstallation(
        Uuid $id,
        ApplicationInstallationStatus $status = ApplicationInstallationStatus::active
    ): ApplicationInstallationInterface {
        $installation = $this->createStub(ApplicationInstallationInterface::class);
        $installation->method('getId')->willReturn($id);
        $installation->method('getStatus')->willReturn($status);

        return $installation;
    }
}
