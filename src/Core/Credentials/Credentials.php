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

namespace Bitrix24\SDK\Core\Credentials;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Application\Requests\Placement\PlacementRequest;

class Credentials
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        protected ?WebhookUrl $webhookUrl,
        protected ?AuthToken $authToken,
        protected ?ApplicationProfile $applicationProfile,
        protected ?Endpoints $endpoints,
    ) {
        if (!$this->authToken instanceof AuthToken && !$this->webhookUrl instanceof WebhookUrl) {
            throw new InvalidArgumentException('you must set on of auth type: webhook or OAuth 2.0');
        }

        if ($this->authToken instanceof AuthToken && !$this->endpoints instanceof \Bitrix24\SDK\Core\Credentials\Endpoints) {
            throw new InvalidArgumentException('for oauth type you must set Endpoints url');
        }

        if ($this->webhookUrl instanceof WebhookUrl && $this->endpoints instanceof Endpoints) {
            throw new InvalidArgumentException('you cannot set Endpoints url for webhook type');
        }
    }

    public function setAuthToken(AuthToken $authToken): void
    {
        $this->authToken = $authToken;
    }

    /**
     * Set domain url
     * @param non-empty-string $domainUrl
     *
     * @throws InvalidArgumentException
     */
    public function changeDomainUrl(string $domainUrl): void
    {
        $parseResult = parse_url($domainUrl);
        if (!array_key_exists('scheme', $parseResult)) {
            $domainUrl = 'https://' . $domainUrl;
        }

        if (filter_var($domainUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(sprintf('domain URL %s is invalid', $domainUrl));
        }

        if ($this->webhookUrl instanceof WebhookUrl) {
            throw new InvalidArgumentException('you cannot change domain url for webhook context');
        }

        $this->endpoints->changeClientUrl($domainUrl);
    }

    public function isWebhookContext(): bool
    {
        return $this->webhookUrl instanceof WebhookUrl && !$this->authToken instanceof AuthToken;
    }

    public function getApplicationProfile(): ?ApplicationProfile
    {
        return $this->applicationProfile;
    }

    public function getDomainUrl(): string
    {
        $arUrl = $this->getWebhookUrl() instanceof WebhookUrl ? parse_url($this->getWebhookUrl()->getUrl()) : parse_url($this->endpoints->getClientUrl());

        return sprintf('%s://%s', $arUrl['scheme'], $arUrl['host']);
    }

    public function getWebhookUrl(): ?WebhookUrl
    {
        return $this->webhookUrl;
    }

    public function getAuthToken(): ?AuthToken
    {
        return $this->authToken;
    }

    public function getEndpoints(): Endpoints
    {
        return $this->endpoints;
    }

    /**
     * Get OAuth server URL
     * @deprecated
     * @todo remove on v1.9.0
     */
    public function getOauthServerUrl(): string
    {
        return $this->endpoints->getAuthServerUrl();
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function createFromWebhook(WebhookUrl $webhookUrl): self
    {
        return new self(
            $webhookUrl,
            null,
            null,
            null
        );
    }

    /**
     *
     * @throws InvalidArgumentException
     */
    public static function createFromOAuth(
        AuthToken $authToken,
        ApplicationProfile $applicationProfile,
        Endpoints $endpoints,
    ): self {
        return new self(
            null,
            $authToken,
            $applicationProfile,
            $endpoints
        );
    }

    /**
     * Create credentials from PlacementRequest
     *
     * @throws InvalidArgumentException
     */
    public static function createFromPlacementRequest(
        PlacementRequest $placementRequest,
        ApplicationProfile $applicationProfile,
        // todo make it required in v2
        ?string $oauthServerUrl = null
    ): self {
        return self::createFromOAuth(
            $placementRequest->getAccessToken(),
            $applicationProfile,
            new Endpoints($placementRequest->getDomainUrl(), $oauthServerUrl)
        );
    }
}