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

use Bitrix24\SDK\Application\Requests\Events\ApplicationLifeCycleEventsFabric;
use Bitrix24\SDK\Application\Requests\Events\ApplicationLifeCycleEventsFactory;
use Bitrix24\SDK\Application\Requests\Events\OnApplicationInstall\OnApplicationInstall;
use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\WrongSecuritySignatureException;
use Bitrix24\SDK\Core\Requests\Events\UnsupportedRemoteEvent;
use Bitrix24\SDK\Services\CRM\Company\Events\CrmCompanyEventsFactory;
use Bitrix24\SDK\Services\Telephony\Events\TelephonyEventsFabric;
use Bitrix24\SDK\Services\Telephony\Events\TelephonyEventsFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class RemoteEventsFactory
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        /**
         * @var  array<int, EventsFabricInterface> $eventsFabrics
         */
        private array $eventsFabrics,
        private LoggerInterface $logger
    ) {
        foreach ($this->eventsFabrics as $eventFabric) {
            if (!$eventFabric instanceof EventsFabricInterface) {
                throw new InvalidArgumentException(
                    sprintf('object %s must implement interface %s', $eventFabric::class, EventsFabricInterface::class)
                );
            }
        }
    }

    /**
     * Check is remote events fabric can process this request and create event object
     *
     * @param Request $request
     * @return bool
     */
    public static function isCanProcess(Request $request): bool
    {
        $payload = [];
        parse_str($request->getContent(), $payload);

        return array_key_exists('event', $payload);
    }

    /**
     * Create event object from remote event request from Bitrix24
     * If event supported in SDK it will create concrete object, in other cases it will be created UnsupportedRemoteEvent object
     *
     * @param Request $request
     * @param non-empty-string|null $applicationToken
     * @return EventInterface
     * @throws InvalidArgumentException
     * @throws WrongSecuritySignatureException
     */
    public function createEvent(Request $request, ?string $applicationToken): EventInterface
    {
        $payload = [];
        parse_str($request->getContent(), $payload);

        if (!array_key_exists('event', $payload)) {
            $this->logger->warning('createEvent.invalidRequestPayload', [
                'event_payload' => $payload
            ]);
            throw new InvalidArgumentException('key «event» not found in request payload');
        }
        if ($applicationToken !== null && trim($applicationToken) === '') {
            throw new InvalidArgumentException('application token cannot be empty');
        }

        $eventCode = $payload['event'];
        $this->logger->debug('createEvent.start', [
            'eventCode' => $eventCode,
            'applicationToken' => $applicationToken
        ]);

        $event = new UnsupportedRemoteEvent($request);
        foreach ($this->eventsFabrics as $itemFabric) {
            if ($itemFabric->isSupport($eventCode)) {
                $event = $itemFabric->create($request);
                break;
            }
        }

        // check event security signature
        // see https://apidocs.bitrix24.com/api-reference/events/safe-event-handlers.html
        // skip OnApplicationInstall event check because application_token is null
        // first event in application lifecycle is OnApplicationInstall and this event contains application_token
        // all next events MUST validate for application_token signature
        if (!$event instanceof OnApplicationInstall) {
            if ($applicationToken !== null) {
                if ($applicationToken !== $event->getAuth()->application_token) {
                    throw new WrongSecuritySignatureException(
                        sprintf(
                            'local application token mismatch with application token from event %s',
                            $event->getEventCode()
                        )
                    );
                }

                $this->logger->debug('createEvent.validSignature');
            } else {
                $this->logger->notice('application_token is null, we cant check security signature for remote events');
            }
        }

        $this->logger->debug('createEvent.finish', [
            'eventClassName' => $event::class,
            'eventCode' => $event->getEventCode()
        ]);
        return $event;
    }

    /**
     * Initializes the EventsFabric with the given logger.
     *
     * @param LoggerInterface $logger The logger to be used for logging events.
     *
     * @return self The initialized EventsFabric instance.
     *
     * @throws InvalidArgumentException If any of the events fabrics in the array do not implement the EventsFabricInterface.
     */
    public static function init(LoggerInterface $logger): self
    {
        return new self(
            [
                // register events fabric by scope
                new ApplicationLifeCycleEventsFactory(),
                new TelephonyEventsFactory(),
                new CrmCompanyEventsFactory(),
            ],
            $logger
        );
    }
}