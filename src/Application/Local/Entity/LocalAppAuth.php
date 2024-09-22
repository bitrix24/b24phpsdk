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

namespace Bitrix24\SDK\Application\Local\Entity;

use Bitrix24\SDK\Core\Credentials\AuthToken;

final class LocalAppAuth
{
    public function __construct(
        private AuthToken       $authToken,
        private readonly string $domainUrl,
        private readonly ?string         $applicationToken)
    {
    }

    public function updateAuthToken(AuthToken $authToken): void
    {
        $this->authToken = $authToken;
    }

    public function getAuthToken(): AuthToken
    {
        return $this->authToken;
    }

    public function getDomainUrl(): string
    {
        return $this->domainUrl;
    }

    public function getApplicationToken(): ?string
    {
        return $this->applicationToken;
    }

    public static function initFromArray(array $localAppAuthPayload): self
    {
        return new self(
            AuthToken::initFromArray($localAppAuthPayload['auth_token']),
            $localAppAuthPayload['domain_url'],
            $localAppAuthPayload['application_token']);
    }

    public function toArray(): array
    {
        return [
            'auth_token' => [
                'access_token' => $this->authToken->accessToken,
                'refresh_token' => $this->authToken->refreshToken,
                'expires' => $this->authToken->expires
            ],
            'domain_url' => $this->domainUrl,
            'application_token' => $this->applicationToken,
        ];
    }
}