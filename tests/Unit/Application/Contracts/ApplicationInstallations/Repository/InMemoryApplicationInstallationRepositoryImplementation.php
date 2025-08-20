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
use Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Entity\Bitrix24AccountStatus;
use Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Repository\Bitrix24AccountRepositoryInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class InMemoryApplicationInstallationRepositoryImplementation implements ApplicationInstallationRepositoryInterface
{
    /**
     * @var ApplicationInstallationInterface[]
     */
    private array $items = [];

    public function __construct(
        private readonly Bitrix24AccountRepositoryInterface $bitrix24AccountRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function save(ApplicationInstallationInterface $applicationInstallation): void
    {
        $this->logger->debug('InMemoryApplicationInstallationRepositoryImplementation.save', ['id' => $applicationInstallation->getId()->toRfc4122()]);

        $this->items[$applicationInstallation->getId()->toRfc4122()] = $applicationInstallation;
    }

    public function delete(Uuid $uuid): void
    {
        $this->logger->debug('InMemoryApplicationInstallationRepositoryImplementation.delete', ['id' => $uuid->toRfc4122()]);

        $applicationInstallation = $this->getById($uuid);
        if (ApplicationInstallationStatus::deleted !== $applicationInstallation->getStatus()) {
            throw new InvalidArgumentException(
                sprintf(
                    'you cannot delete application installation «%s», in status «%s», mark applicatoin installation as «deleted» before',
                    $applicationInstallation->getId()->toRfc4122(),
                    $applicationInstallation->getStatus()->name,
                )
            );
        }

        unset($this->items[$uuid->toRfc4122()]);
    }

    public function getById(Uuid $uuid): ApplicationInstallationInterface
    {
        $this->logger->debug('InMemoryApplicationInstallationRepositoryImplementation.getById', ['id' => $uuid->toRfc4122()]);

        if (!array_key_exists($uuid->toRfc4122(), $this->items)) {
            throw new ApplicationInstallationNotFoundException(sprintf('application installation not found by id «%s» ', $uuid->toRfc4122()));
        }

        return $this->items[$uuid->toRfc4122()];
    }

    public function findByBitrix24AccountId(Uuid $uuid): ?ApplicationInstallationInterface
    {
        $this->logger->debug('InMemoryApplicationInstallationRepositoryImplementation.findByBitrix24AccountId', ['id' => $uuid->toRfc4122()]);

        foreach ($this->items as $item) {
            if ($item->getBitrix24AccountId() === $uuid) {
                return $item;
            }
        }

        return null;
    }

    public function findByExternalId(string $externalId): array
    {
        $this->logger->debug('InMemoryApplicationInstallationRepositoryImplementation.findByExternalId', ['externalId' => $externalId]);
        if (trim($externalId) === '') {
            throw new InvalidArgumentException('external id cannot be empty string');
        }

        $result = [];
        foreach ($this->items as $item) {
            if ($item->getExternalId() === $externalId) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function findByMemberId(string $memberId): ?ApplicationInstallationInterface
    {
        $this->logger->debug('InMemoryApplicationInstallationRepositoryImplementation.findByMemberId', ['memberId' => $memberId]);

        if (trim($memberId) === '') {
            throw new InvalidArgumentException('memberId id cannot be empty string');
        }

        $b24Accounts = $this->bitrix24AccountRepository->findByMemberId(
            $memberId,
            Bitrix24AccountStatus::active,
            null,
            null,
            true
        );
        $b24Account = null;
        if ($b24Accounts !== []) {
            $b24Account = $b24Accounts[0];
        }

        if ($b24Account === null) {
            return null;
        }

        foreach ($this->items as $item) {
            if ($item->getBitrix24AccountId()->equals($b24Account->getId())) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function findByApplicationToken(string $applicationToken): ?ApplicationInstallationInterface
    {
        $this->logger->debug('InMemoryApplicationInstallationRepositoryImplementation.findByApplicationToken', ['applicationToken' => $applicationToken]);

        if (trim($applicationToken) === '') {
            throw new InvalidArgumentException('applicationToken id cannot be empty string');
        }

        foreach ($this->items as $item) {
            if ($item->isApplicationTokenValid($applicationToken)) {
                return $item;
            }
        }

        return null;
    }
}