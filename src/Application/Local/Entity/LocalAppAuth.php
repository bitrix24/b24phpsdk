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
use Bitrix24\SDK\Core\Credentials\DefaultOAuthServerUrl;

final class LocalAppAuth
{
    public function __construct(
        private AuthToken        $authToken,
        private readonly string  $domainUrl,
        private readonly ?string $applicationToken,
        private readonly string  $oauthServerUrl)
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

    public function getOAuthServerUrl(): string
    {
        return $this->oauthServerUrl;
    }

    public static function initFromArray(array $localAppAuthPayload): self
    {
        return new self(
            AuthToken::initFromArray($localAppAuthPayload['auth_token']),
            $localAppAuthPayload['domain_url'],
            $localAppAuthPayload['application_token'],
            $localAppAuthPayload['oauth_server_url'] ?? DefaultOAuthServerUrl::default());
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
            'oauth_server_url' => $this->oauthServerUrl,
        ];
    }
}