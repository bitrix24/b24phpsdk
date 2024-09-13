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

namespace Bitrix24\SDK\Application\Local\Infrastructure\Filesystem;

use Bitrix24\SDK\Application\Local\Entity\LocalAppAuth;
use Bitrix24\SDK\Application\Local\Repository\LocalAppAuthRepositoryInterface;
use Bitrix24\SDK\Core\Exceptions\FileNotFoundException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\RenewedAuthToken;
use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

readonly class AppAuthFileStorage implements LocalAppAuthRepositoryInterface
{
    public function __construct(
        private string          $authFileName,
        private Filesystem      $filesystem,
        private LoggerInterface $logger
    )
    {
    }

    public function isAvailable(): bool
    {
        return $this->filesystem->exists($this->authFileName);
    }

    /**
     * @throws FileNotFoundException
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    public function get(): LocalAppAuth
    {
        $this->logger->debug('AppAuthFileStorage.get.start', [
            'authFileName' => $this->authFileName
        ]);

        if (!$this->filesystem->exists($this->authFileName)) {
            throw new FileNotFoundException(sprintf('file «%s» with stored access token not found', $this->authFileName));
        }

        $payload = file_get_contents($this->authFileName);
        $appAuthPayload = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        if ($appAuthPayload === null) {
            throw new InvalidArgumentException('local app auth is empty');
        }
        $appAuthPayload = LocalAppAuth::initFromArray($appAuthPayload);
        $this->logger->debug('AppAuthFileStorage.get.finish');

        return $appAuthPayload;
    }

    /**
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function saveRenewedToken(RenewedAuthToken $renewedAuthToken): void
    {
        $this->logger->debug('AppAuthFileStorage.saveRenewedToken.start');
        $updatedAuth = $this->get();
        $updatedAuth->updateAuthToken($renewedAuthToken->authToken);

        $this->save($updatedAuth);
        $this->logger->debug('AppAuthFileStorage.saveRenewedToken.finish');
    }

    /**
     * @throws JsonException
     */
    public function save(LocalAppAuth $appAuth): void
    {
        $this->logger->debug('AppAuthFileStorage.save.start', [
            'authFileName' => $this->authFileName
        ]);

        $tokenPayload = json_encode($appAuth->toArray(), JSON_THROW_ON_ERROR);
        $this->filesystem->dumpFile($this->authFileName, $tokenPayload);

        $this->logger->debug('AppAuthFileStorage.save.finish', [
            'tokenPayloadSize' => strlen($tokenPayload),
        ]);
    }
}