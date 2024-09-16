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

namespace App;

use Bitrix24\SDK\Application\Local\Entity\LocalAppAuth;
use Bitrix24\SDK\Application\Local\Infrastructure\Filesystem\AppAuthFileStorage;
use Bitrix24\SDK\Application\Local\Repository\LocalAppAuthRepositoryInterface;
use Bitrix24\SDK\Application\Requests\Events\ApplicationLifeCycleEventsFabric;
use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\UnknownScopeCodeException;
use Bitrix24\SDK\Core\Exceptions\WrongConfigurationException;
use Bitrix24\SDK\Events\AuthTokenRenewedEvent;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Application
{
    private const CONFIG_FILE_NAME = '/config/.env';

    private const LOG_FILE_NAME = '/var/log/application.log';

    public static function processEvents(Request $incomingRequest): Response
    {
        self::getLog()->debug('processEvents.start', ['request' => $incomingRequest->request->all()]);

        $response = new Response();
        try {
            // todo create global event fabric
            $event = (new ApplicationLifeCycleEventsFabric())->create($incomingRequest);
            if (!$event instanceof \Bitrix24\SDK\Application\Requests\Events\EventInterface) {
                throw new InvalidArgumentException('unknown event');
            }

            self::getLog()->debug('event.received', [
                'eventCode' => $event->getEventCode(),
                'eventPayload' => $event->getEventPayload()
            ]);

            switch ($event->getEventCode()) {
                case 'ONAPPINSTALL':
                    // save auth tokens and application token
                    self::getAuthRepository()->save(
                        new LocalAppAuth(
                            $event->getAuth()->authToken,
                            $event->getAuth()->domain,
                            $event->getAuth()->application_token
                        )
                    );
                    break;
                case 'OTHER_EVENT_CODE':


                    // add your event handler code
                    break;
            }

            $response->setContent('OK');
        } catch (\Throwable $throwable) {
            self::getLog()->error('processEvents.error', [
                'message' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString(),
                'event_payload' => $incomingRequest->request->all()
            ]);

            $response->setStatusCode(500);
            $response->setContent($throwable->getMessage());
        }

        return $response;
    }

    /**
     * @throws WrongConfigurationException
     * @throws InvalidArgumentException
     */
    public static function getLog(): LoggerInterface
    {
        static $logger;

        if ($logger === null) {
            // load config
            self::loadConfigFromEnvFile();

            // check settings
            if (!array_key_exists('BITRIX24_PHP_SDK_LOG_LEVEL', $_ENV)) {
                throw new InvalidArgumentException('in $_ENV variables not found key BITRIX24_PHP_SDK_LOG_LEVEL');
            }

            // rotating
            $rotatingFileHandler = new RotatingFileHandler(dirname(__DIR__) . self::LOG_FILE_NAME, 0, (int)$_ENV['BITRIX24_PHP_SDK_LOG_LEVEL']);
            $rotatingFileHandler->setFilenameFormat('{filename}-{date}', 'Y-m-d');

            $logger = new Logger('App');
            $logger->pushHandler($rotatingFileHandler);
            $logger->pushProcessor(new MemoryUsageProcessor(true, true));
            $logger->pushProcessor(new UidProcessor());
        }

        return $logger;
    }

    /**
     * Retrieves an instance of the event dispatcher.
     *
     * @return EventDispatcherInterface The event dispatcher instance.
     */
    protected static function getEventDispatcher(): EventDispatcherInterface
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(AuthTokenRenewedEvent::class, function (AuthTokenRenewedEvent $authTokenRenewedEvent): void {
            self::onAuthTokenRenewedEventListener($authTokenRenewedEvent);
        });
        return $eventDispatcher;
    }

    /**
     * Event listener for when the authentication token is renewed.
     *
     * @param AuthTokenRenewedEvent $authTokenRenewedEvent The event object containing the renewed authentication token.
     */
    protected static function onAuthTokenRenewedEventListener(AuthTokenRenewedEvent $authTokenRenewedEvent): void
    {
        self::getLog()->debug('onAuthTokenRenewedEventListener.start', [
            'expires' => $authTokenRenewedEvent->getRenewedToken()->authToken->expires
        ]);

        // save renewed auth token
        self::getAuthRepository()->saveRenewedToken($authTokenRenewedEvent->getRenewedToken());

        self::getLog()->debug('onAuthTokenRenewedEventListener.finish');
    }

    /**
     * Retrieves an instance of the B24Service.
     *
     * @return ServiceBuilder The B24Service instance.
     * @throws InvalidArgumentException
     * @throws UnknownScopeCodeException
     * @throws WrongConfigurationException
     */
    public static function getB24Service(): ServiceBuilder
    {
        self::getLog()->debug('getB24Service.start');

        return (new ServiceBuilderFactory(
            self::getEventDispatcher(),
            self::getLog()
        ))->init(
        // load app profile from /config/.env.local to $_ENV and create ApplicationProfile object
            self::getApplicationProfile(),
            // load oauth tokens and portal URL stored in /config/auth.json.local to LocalAppAuth object
            self::getAuthRepository()->get()->getAuthToken(),
            self::getAuthRepository()->get()->getDomainUrl()
        );
    }

    /**
     * Retrieves the authentication repository.
     *
     * @return LocalAppAuthRepositoryInterface The authentication repository used for B24Service.
     */
    protected static function getAuthRepository(): LocalAppAuthRepositoryInterface
    {
        return new AppAuthFileStorage(
            dirname(__DIR__) . '/config/auth.json.local',
            new Filesystem(),
            self::getLog()
        );
    }

    /**
     * Get Application profile from environment variables
     *
     * By default behavioral
     *
     * @throws WrongConfigurationException
     * @throws UnknownScopeCodeException
     * @throws InvalidArgumentException
     */
    protected static function getApplicationProfile(): ApplicationProfile
    {
        self::getLog()->debug('getApplicationProfile.start');
        // you can find list of local apps by this URL
        // https://YOUR-PORTAL-URL.bitrix24.com/devops/list/
        // or see in left menu Developer resources → Integrations → select target local applicatoin

        // load config: application secrets, logging
        self::loadConfigFromEnvFile();

        try {
            $profile = ApplicationProfile::initFromArray($_ENV);
            self::getLog()->debug('getApplicationProfile.finish');
            return $profile;
        } catch (InvalidArgumentException $invalidArgumentException) {
            self::getLog()->error('getApplicationProfile.error',
                [
                    'message' => sprintf('cannot read config from $_ENV: %s', $invalidArgumentException->getMessage()),
                    'trace' => $invalidArgumentException->getTraceAsString()
                ]);
            throw $invalidArgumentException;
        }
    }

    /**
     * Loads configuration from the environment file.
     *
     * @throws WrongConfigurationException if "symfony/dotenv" is not added as a Composer dependency.
     */
    private static function loadConfigFromEnvFile(): void
    {
        static $isConfigLoaded = null;
        if ($isConfigLoaded === null) {
            if (!class_exists(Dotenv::class)) {
                throw new WrongConfigurationException('You need to add "symfony/dotenv" as Composer dependencies.');
            }

            $argvInput = new ArgvInput();
            if (null !== $env = $argvInput->getParameterOption(['--env', '-e'], null, true)) {
                putenv('APP_ENV=' . $_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $env);
            }

            if ($argvInput->hasParameterOption('--no-debug', true)) {
                putenv('APP_DEBUG=' . $_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '0');
            }

            (new Dotenv())->loadEnv(dirname(__DIR__) . self::CONFIG_FILE_NAME);

            $isConfigLoaded = true;
        }
    }
}