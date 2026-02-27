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
use Bitrix24\SDK\Application\PortalLicenseFamily;
use Bitrix24\SDK\Tests\Application\Contracts\ApplicationInstallations\Repository\ApplicationInstallationRepositoryInterfaceTest;
use Bitrix24\SDK\Tests\Application\Contracts\NullableFlusher;
use Bitrix24\SDK\Tests\Application\Contracts\TestRepositoryFlusherInterface;
use Bitrix24\SDK\Tests\Unit\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationReferenceEntityImplementation;
use Bitrix24\SDK\Tests\Unit\Application\Contracts\Bitrix24Accounts\Repository\InMemoryBitrix24AccountRepositoryImplementation;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ApplicationInstallationRepositoryInterface::class)]
class InMemoryApplicationInstallationRepositoryImplementationTest extends ApplicationInstallationRepositoryInterfaceTest
{
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
        return new InMemoryApplicationInstallationRepositoryImplementation(
            new InMemoryBitrix24AccountRepositoryImplementation(new NullLogger()),
            new NullLogger()
        );
    }
}