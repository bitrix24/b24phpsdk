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
use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Requests\Events\UnsupportedRemoteEvent;
use Bitrix24\SDK\Services\Telephony\Events\TelephonyEventsFabric;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class RemoteEventsFabric
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        /**
         * @var  array<int, EventsFabricInterface> $eventsFabrics
         */
        private array           $eventsFabrics,
        private LoggerInterface $logger
    )
    {
        foreach ($this->eventsFabrics as $eventFabric) {
            if (!$eventFabric instanceof EventsFabricInterface) {
                throw new InvalidArgumentException(sprintf('object %s must implement interface %s', $eventFabric::class, EventsFabricInterface::class));
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
     * Create events objects from incoming events from Bitrix24
     *
     * @throws InvalidArgumentException
     */
    public function createEvent(Request $request): EventInterface
    {
        $payload = [];
        parse_str($request->getContent(), $payload);
        if (!array_key_exists('event', $payload)) {
            throw new InvalidArgumentException('key «event» not found in request payload');
        }
        $eventCode = $payload['event'];
        $this->logger->debug('createEvent.start', [
            'eventCode' => $eventCode
        ]);

        $event = new UnsupportedRemoteEvent($request);
        foreach ($this->eventsFabrics as $itemFabric) {
            if ($itemFabric->isSupport($eventCode)) {
                $event = $itemFabric->create($request);
                break;
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
                new ApplicationLifeCycleEventsFabric(),
                new TelephonyEventsFabric(),
            ],
            $logger
        );
    }
}