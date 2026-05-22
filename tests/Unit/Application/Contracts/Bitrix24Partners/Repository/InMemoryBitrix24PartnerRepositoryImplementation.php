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

namespace Bitrix24\SDK\Tests\Unit\Application\Contracts\Bitrix24Partners\Repository;

use Bitrix24\SDK\Application\Contracts\Bitrix24Partners\Entity\Bitrix24PartnerInterface;
use Bitrix24\SDK\Application\Contracts\Bitrix24Partners\Entity\Bitrix24PartnerStatus;
use Bitrix24\SDK\Application\Contracts\Bitrix24Partners\Exceptions\Bitrix24PartnerNotFoundException;
use Bitrix24\SDK\Application\Contracts\Bitrix24Partners\Repository\Bitrix24PartnerRepositoryInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class InMemoryBitrix24PartnerRepositoryImplementation implements Bitrix24PartnerRepositoryInterface
{
    /**
     * @var Bitrix24PartnerInterface[]
     */
    private array $items = [];

    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    #[\Override]
    public function findByBitrix24PartnerNumber(int $bitrix24PartnerNumber): ?Bitrix24PartnerInterface
    {
        $this->logger->debug('b24PartnerRepository.findByBitrix24PartnerNumber', [
            'bitrix24PartnerNumber' => $bitrix24PartnerNumber
        ]);

        foreach ($this->items as $item) {
            if ($item->getBitrix24PartnerNumber() === $bitrix24PartnerNumber) {
                $this->logger->debug('b24PartnerRepository.findByBitrix24PartnerNumber.found', [
                    'id' => $item->getId()->toRfc4122()
                ]);
                return $item;
            }
        }

        return null;
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    public function findByTitle(string $title): array
    {
        $this->logger->debug('b24PartnerRepository.findByTitle', [
            'title' => $title
        ]);

        if (trim($title) === '') {
            throw new InvalidArgumentException('you cant find by empty title');
        }

        $title = strtolower(trim($title));

        $items = [];
        foreach ($this->items as $item) {
            if (strtolower($item->getTitle()) === $title) {
                $this->logger->debug('b24PartnerRepository.findByTitle.found', [
                    'id' => $item->getId()->toRfc4122()
                ]);
                $items[] = $item;
            }
        }

        return $items;
    }

    #[\Override]
    public function findByExternalId(string $externalId, ?Bitrix24PartnerStatus $bitrix24PartnerStatus = null): array
    {
        $this->logger->debug('b24PartnerRepository.findByExternalId', [
            'externalId' => $externalId,
            'bitrix24PartnerStatus' => $bitrix24PartnerStatus?->name
        ]);

        if (trim($externalId) === '') {
            throw new InvalidArgumentException('you cant find by empty externalId');
        }

        $externalId = trim($externalId);

        $items = [];
        foreach ($this->items as $item) {
            if ($item->getExternalId() === $externalId && (is_null($bitrix24PartnerStatus) || $item->getStatus() === $bitrix24PartnerStatus)) {
                $this->logger->debug('b24PartnerRepository.findByExternalId.found', [
                    'id' => $item->getId()->toRfc4122()
                ]);
                $items[] = $item;
            }
        }

        return $items;
    }

    #[\Override]
    public function save(Bitrix24PartnerInterface $bitrix24Partner): void
    {
        $this->logger->debug('b24PartnerRepository.save', [
            'id' => $bitrix24Partner->getId()->toRfc4122(),
            'bitrix24PartnerNumber' => $bitrix24Partner->getBitrix24PartnerNumber()
        ]);

        $this->items[$bitrix24Partner->getId()->toRfc4122()] = $bitrix24Partner;
    }

    #[\Override]
    public function getById(Uuid $uuid): Bitrix24PartnerInterface
    {
        $this->logger->debug('b24PartnerRepository.getById', ['id' => $uuid->toRfc4122()]);

        if (!array_key_exists($uuid->toRfc4122(), $this->items)) {
            throw new Bitrix24PartnerNotFoundException(sprintf('bitrix24 partner not found by id «%s» ', $uuid->toRfc4122()));
        }

        return $this->items[$uuid->toRfc4122()];
    }
}
