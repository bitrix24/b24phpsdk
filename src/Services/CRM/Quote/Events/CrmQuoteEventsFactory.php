<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Quote\Events;

use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\CRM\Quote\Events\OnCrmQuoteAdd\OnCrmQuoteAdd;
use Bitrix24\SDK\Services\CRM\Quote\Events\OnCrmQuoteDelete\OnCrmQuoteDelete;
use Bitrix24\SDK\Services\CRM\Quote\Events\OnCrmQuoteUpdate\OnCrmQuoteUpdate;
use Bitrix24\SDK\Services\CRM\Quote\Events\OnCrmQuoteUserFieldAdd\OnCrmQuoteUserFieldAdd;
use Bitrix24\SDK\Services\CRM\Quote\Events\OnCrmQuoteUserFieldDelete\OnCrmQuoteUserFieldDelete;
use Bitrix24\SDK\Services\CRM\Quote\Events\OnCrmQuoteUserFieldSetEnumValues\OnCrmQuoteUserFieldSetEnumValues;
use Bitrix24\SDK\Services\CRM\Quote\Events\OnCrmQuoteUserFieldUpdate\OnCrmQuoteUserFieldUpdate;
use Symfony\Component\HttpFoundation\Request;

readonly class CrmQuoteEventsFactory implements EventsFabricInterface
{
    public function isSupport(string $eventCode): bool
    {
        return in_array(strtoupper($eventCode), [
            OnCrmQuoteAdd::CODE,
            OnCrmQuoteUpdate::CODE,
            OnCrmQuoteDelete::CODE,
            OnCrmQuoteUserFieldAdd::CODE,
            OnCrmQuoteUserFieldUpdate::CODE,
            OnCrmQuoteUserFieldDelete::CODE,
            OnCrmQuoteUserFieldSetEnumValues::CODE,
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
            OnCrmQuoteAdd::CODE => new OnCrmQuoteAdd($eventRequest),
            OnCrmQuoteUpdate::CODE => new OnCrmQuoteUpdate($eventRequest),
            OnCrmQuoteDelete::CODE => new OnCrmQuoteDelete($eventRequest),
            OnCrmQuoteUserFieldAdd::CODE => new OnCrmQuoteUserFieldAdd($eventRequest),
            OnCrmQuoteUserFieldUpdate::CODE => new OnCrmQuoteUserFieldUpdate($eventRequest),
            OnCrmQuoteUserFieldDelete::CODE => new OnCrmQuoteUserFieldDelete($eventRequest),
            OnCrmQuoteUserFieldSetEnumValues::CODE => new OnCrmQuoteUserFieldSetEnumValues($eventRequest),
            default => throw new InvalidArgumentException(
                sprintf('Unexpected event code «%s»', $eventPayload['event'])
            ),
        };
    }
}
