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
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Telephony\Events\TelephonyEventsFabric;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Bitrix24\SDK\Application\Requests\Events\ApplicationLifeCycleEventsFabric;

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
     * Init service builder from application account
     *
     * @param ApplicationProfile $applicationProfile
     * @param Bitrix24AccountInterface $bitrix24Account
     *
     * @return ServiceBuilder
     * @throws InvalidArgumentException
     */
    public function initFromAccount(ApplicationProfile $applicationProfile, Bitrix24AccountInterface $bitrix24Account): ServiceBuilder
    {
        return $this->getServiceBuilder(
            Credentials::createFromOAuth(
                $bitrix24Account->getAuthToken(),
                $applicationProfile,
                $bitrix24Account->getDomainUrl()
            )
        );
    }

    /**
     * Init service builder
     *
     * @param ApplicationProfile $applicationProfile
     * @param AuthToken $authToken
     * @param string $bitrix24DomainUrl
     *
     * @return ServiceBuilder
     * @throws InvalidArgumentException
     */
    public function init(
        ApplicationProfile $applicationProfile,
        AuthToken          $authToken,
        string             $bitrix24DomainUrl
    ): ServiceBuilder
    {
        return $this->getServiceBuilder(
            Credentials::createFromOAuth(
                $authToken,
                $applicationProfile,
                $bitrix24DomainUrl
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
            new EventsFabric(
                [
                    new ApplicationLifeCycleEventsFabric(),
                    new TelephonyEventsFabric(),
                ],
                $this->log),
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
        string                    $webhookUrl,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface          $logger = null): ServiceBuilder
    {
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
        Request                   $placementRequest,
        ApplicationProfile        $applicationProfile,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface          $logger = null
    ): ServiceBuilder
    {
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

        return (new ServiceBuilderFactory($eventDispatcher, $logger))
            ->init(
                $applicationProfile,
                AuthToken::initFromPlacementRequest($placementRequest),
                $rawDomainUrl
            );
    }
}