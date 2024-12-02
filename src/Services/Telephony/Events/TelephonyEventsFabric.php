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


namespace Bitrix24\SDK\Services\Telephony\Events;


use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\RemoteEventsFactory;
use Bitrix24\SDK\Services\Telephony\Events\OnExternalCallBackStart\OnExternalCallBackStart;
use Bitrix24\SDK\Services\Telephony\Events\OnExternalCallStart\OnExternalCallStart;
use Bitrix24\SDK\Services\Telephony\Events\OnVoximplantCallEnd\OnVoximplantCallEnd;
use Bitrix24\SDK\Services\Telephony\Events\OnVoximplantCallInit\OnVoximplantCallInit;
use Bitrix24\SDK\Services\Telephony\Events\OnVoximplantCallStart\OnVoximplantCallStart;
use JetBrains\PhpStorm\Deprecated;
use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated wrong class name, class will be deleted, use TelephonyEventsFactory
 */
readonly class TelephonyEventsFabric implements EventsFabricInterface
{
    public function isSupport(string $eventCode): bool
    {
        return in_array(strtoupper($eventCode), [
            OnExternalCallBackStart::CODE,
            OnExternalCallStart::CODE,
            OnVoximplantCallEnd::CODE,
            OnVoximplantCallInit::CODE,
            OnVoximplantCallStart::CODE,
        ], true);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function create(Request $eventRequest): EventInterface
    {
        $eventPayload = $eventRequest->request->all();
        if (!array_key_exists('event', $eventPayload)) {
            throw new InvalidArgumentException('«event» key not found in event payload');
        }

        return match ($eventPayload['event']) {
            OnExternalCallBackStart::CODE => new OnExternalCallBackStart($eventRequest),
            OnExternalCallStart::CODE => new OnExternalCallStart($eventRequest),
            OnVoximplantCallEnd::CODE => new OnVoximplantCallEnd($eventRequest),
            OnVoximplantCallInit::CODE => new OnVoximplantCallInit($eventRequest),
            OnVoximplantCallStart::CODE => new OnVoximplantCallStart($eventRequest),
            default => throw new InvalidArgumentException(sprintf('Unexpected event code «%s»',  $eventPayload['event'])),
        };
    }
}