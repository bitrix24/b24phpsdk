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

namespace Bitrix24\SDK\Services;

use Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Entity\Bitrix24AccountInterface;
use Bitrix24\SDK\Core\Batch;
use Bitrix24\SDK\Core\BulkItemsReader\BulkItemsReaderBuilder;
use Bitrix24\SDK\Core\CoreBuilder;
use Bitrix24\SDK\Core\Credentials\AuthToken;
use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\DefaultOAuthServerUrl;
use Bitrix24\SDK\Core\Credentials\Endpoints;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ServiceBuilderFactory
{
    private EventDispatcherInterface $eventDispatcher;
    private LoggerInterface $log;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface $log
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, LoggerInterface $log)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->log = $log;
    }

    /**
     * Init service builder from an application account
     *
     * @param ApplicationProfile $applicationProfile
     * @param Bitrix24AccountInterface $bitrix24Account
     * @param non-empty-string|null $oauthServerUrl
     * @return ServiceBuilder
     * @throws InvalidArgumentException
     */
    public function initFromAccount(
        ApplicationProfile $applicationProfile,
        Bitrix24AccountInterface $bitrix24Account,
        // todo make it required in v2
        ?string $oauthServerUrl = null
    ): ServiceBuilder {
        if ($oauthServerUrl === null) {
            $this->log->warning('oauthServerUrl not set, you must set it manually or use DefaultOAuthServerUrl presets');
            $endpoints = new Endpoints($bitrix24Account->getDomainUrl(), DefaultOAuthServerUrl::default());
        } else {
            $endpoints = new Endpoints($bitrix24Account->getDomainUrl(), $oauthServerUrl);
        }

        return $this->getServiceBuilder(
            Credentials::createFromOAuth(
                $bitrix24Account->getAuthToken(),
                $applicationProfile,
                $endpoints
            )
        );
    }

    /**
     * Init service builder
     *
     * @param ApplicationProfile $applicationProfile
     * @param AuthToken $authToken
     * @param non-empty-string $bitrix24DomainUrl
     * @param string $oauthServerUrl
     * @return ServiceBuilder
     * @throws InvalidArgumentException
     */
    public function init(
        ApplicationProfile $applicationProfile,
        AuthToken $authToken,
        string $bitrix24DomainUrl,
        string $oauthServerUrl
    ): ServiceBuilder {
        return $this->getServiceBuilder(
            Credentials::createFromOAuth(
                $authToken,
                $applicationProfile,
                new Endpoints($bitrix24DomainUrl, $oauthServerUrl)
            )
        );
    }

    /**
     * Init service builder from webhook
     *
     * @param string $webhookUrl
     *
     * @return ServiceBuilder
     * @throws InvalidArgumentException
     */
    public function initFromWebhook(string $webhookUrl): ServiceBuilder
    {
        return $this->getServiceBuilder(Credentials::createFromWebhook(new WebhookUrl($webhookUrl)));
    }

    /**
     * @param Credentials $credentials
     *
     * @return ServiceBuilder
     * @throws InvalidArgumentException
     */
    private function getServiceBuilder(Credentials $credentials): ServiceBuilder
    {
        $core = (new CoreBuilder())
            ->withEventDispatcher($this->eventDispatcher)
            ->withLogger($this->log)
            ->withCredentials($credentials)
            ->build();
        $batch = new Batch($core, $this->log);

        return new ServiceBuilder(
            $core,
            $batch,
            (new BulkItemsReaderBuilder(
                $core,
                $batch,
                $this->log
            ))->build(),
            $this->log
        );
    }

    /**
     * Create service builder from incoming webhook
     *
     * @param non-empty-string $webhookUrl incoming webhook url from your bitrix24 portal
     * @param EventDispatcherInterface|null $eventDispatcher optional event dispatcher for subscribe some domain events if need
     * @param LoggerInterface|null $logger optional logger for debug logs
     * @throws InvalidArgumentException
     */
    public static function createServiceBuilderFromWebhook(
        string $webhookUrl,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface $logger = null
    ): ServiceBuilder {
        if ($eventDispatcher === null) {
            $eventDispatcher = new EventDispatcher();
        }
        if ($logger === null) {
            $logger = new NullLogger();
        }
        return (new ServiceBuilderFactory($eventDispatcher, $logger))->initFromWebhook($webhookUrl);
    }

    /**
     * Create service builder from placement request
     *
     * @param Request $placementRequest The placement request object that contains the request data.
     * @param ApplicationProfile $applicationProfile The application profile object.
     * @param EventDispatcherInterface|null $eventDispatcher Optional event dispatcher for subscribing to domain events.
     * @param LoggerInterface|null $logger Optional logger for debug logs.
     * @return ServiceBuilder The service builder object.
     * @throws InvalidArgumentException If the key "DOMAIN" is not found in the request.
     */
    public static function createServiceBuilderFromPlacementRequest(
        Request $placementRequest,
        ApplicationProfile $applicationProfile,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface $logger = null,
        ?string $oauthServerUrl = null
    ): ServiceBuilder {
        if (!in_array('DOMAIN', $placementRequest->query->keys(), true)) {
            throw new InvalidArgumentException('key «DOMAIN» not found in GET request arguments');
        }

        $rawDomainUrl = trim((string)$placementRequest->query->get('DOMAIN'));
        if ($rawDomainUrl === '') {
            throw new InvalidArgumentException('DOMAIN key cannot be empty in request');
        }

        if ($eventDispatcher === null) {
            $eventDispatcher = new EventDispatcher();
        }
        if ($logger === null) {
            $logger = new NullLogger();
        }

        if ($oauthServerUrl === null) {
            $logger->warning('oauthServerUrl not set, you must set it manually or use DefaultOAuthServerUrl presets');
            $oauthServerUrl = DefaultOAuthServerUrl::default();
        }

        return (new ServiceBuilderFactory($eventDispatcher, $logger))
            ->init(
                $applicationProfile,
                AuthToken::initFromPlacementRequest($placementRequest),
                $rawDomainUrl,
                $oauthServerUrl
            );
    }
}