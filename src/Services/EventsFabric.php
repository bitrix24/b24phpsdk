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

use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Requests\Events\UnsupportedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class EventsFabric
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

        $event = new UnsupportedEvent($request);
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
}