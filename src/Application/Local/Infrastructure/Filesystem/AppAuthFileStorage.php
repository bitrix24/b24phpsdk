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

    public function getApplicationToken(): ?string
    {
        $this->logger->debug('AppAuthFileStorage.getApplicationToken.start', [
            'authFileName' => $this->authFileName
        ]);

        if (!$this->filesystem->exists($this->authFileName)) {
            $this->logger->debug('AppAuthFileStorage.getApplicationToken.fileNotFound');
            return null;
        }

        $localAppAuth = $this->getPayload();
        $this->logger->debug('AppAuthFileStorage.getApplicationToken.finish');

        return $localAppAuth->getApplicationToken();
    }

    /**
     * Retrieves the local app authentication details.
     *
     * This method retrieves the local app authentication details by reading the contents of a specific file.
     * It starts by logging a debug message with the start event. Then, it checks if the specified file exists.
     * If the file does not exist, it throws a FileNotFoundException indicating that the file with the stored access token was not found.
     * If the file exists, it reads the contents of the file and decodes it using JSON.
     **/
    public function getAuth(): LocalAppAuth
    {
        $this->logger->debug('AppAuthFileStorage.getAuth.start', [
            'authFileName' => $this->authFileName
        ]);

        if (!$this->filesystem->exists($this->authFileName)) {
            throw new FileNotFoundException(sprintf('file «%s» with stored access token not found', $this->authFileName));
        }

        $localAppAuth = $this->getPayload();
        $this->logger->debug('AppAuthFileStorage.getAuth.finish');

        return $localAppAuth;
    }

    private function getPayload(): LocalAppAuth
    {
        $payload = file_get_contents($this->authFileName);
        $appAuthPayload = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        if ($appAuthPayload === null) {
            throw new InvalidArgumentException('local app auth is empty');
        }

        return LocalAppAuth::initFromArray($appAuthPayload);
    }

    /**
     * Saves a renewed authentication token.
     *
     * @param RenewedAuthToken $renewedAuthToken The renewed authentication token to be saved.
     *
     * @throws FileNotFoundException If the file with the stored access token is not found.
     * @throws JsonException If there is an error decoding the local app auth payload.
     * @throws InvalidArgumentException If the local app auth is empty.
     */
    public function saveRenewedToken(RenewedAuthToken $renewedAuthToken): void
    {
        $this->logger->debug('AppAuthFileStorage.saveRenewedToken.start');
        $localAppAuth = $this->getAuth();
        $localAppAuth->updateAuthToken($renewedAuthToken->authToken);

        $this->save($localAppAuth);
        $this->logger->debug('AppAuthFileStorage.saveRenewedToken.finish');
    }

    /**
     * Saves the given LocalAppAuth object to a file.
     *
     * @param LocalAppAuth $localAppAuth The LocalAppAuth object to be saved.
     * @throws JsonException If the JSON encoding fails.
     */
    public function save(LocalAppAuth $localAppAuth): void
    {
        $this->logger->debug('AppAuthFileStorage.save.start', [
            'authFileName' => $this->authFileName
        ]);

        $tokenPayload = json_encode($localAppAuth->toArray(), JSON_THROW_ON_ERROR);
        $this->filesystem->dumpFile($this->authFileName, $tokenPayload);

        $this->logger->debug('AppAuthFileStorage.save.finish', [
            'tokenPayloadSize' => strlen($tokenPayload),
        ]);
    }
}