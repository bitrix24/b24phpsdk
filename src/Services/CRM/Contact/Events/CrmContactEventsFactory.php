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

namespace Bitrix24\SDK\Services\CRM\Contact\Events;

use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\CRM\Contact\Events\OnCrmContactAdd\OnCrmContactAdd;
use Bitrix24\SDK\Services\CRM\Contact\Events\OnCrmContactDelete\OnCrmContactDelete;
use Bitrix24\SDK\Services\CRM\Contact\Events\OnCrmContactUpdate\OnCrmContactUpdate;
use Symfony\Component\HttpFoundation\Request;

readonly class CrmContactEventsFactory implements EventsFabricInterface
{
    public function isSupport(string $eventCode): bool
    {
        return in_array(strtoupper($eventCode), [
            OnCrmContactAdd::CODE,
            OnCrmContactUpdate::CODE,
            OnCrmContactDelete::CODE,
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
            OnCrmContactAdd::CODE => new OnCrmContactAdd($eventRequest),
            OnCrmContactUpdate::CODE => new OnCrmContactUpdate($eventRequest),
            OnCrmContactDelete::CODE => new OnCrmContactDelete($eventRequest),
            default => throw new InvalidArgumentException(
                sprintf('Unexpected event code «%s»', $eventPayload['event'])
            ),
        };
    }
}
