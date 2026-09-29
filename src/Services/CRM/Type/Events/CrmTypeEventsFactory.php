<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Type\Events;

use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\CRM\Type\Events\OnCrmTypeUserFieldAdd\OnCrmTypeUserFieldAdd;
use Bitrix24\SDK\Services\CRM\Type\Events\OnCrmTypeUserFieldDelete\OnCrmTypeUserFieldDelete;
use Bitrix24\SDK\Services\CRM\Type\Events\OnCrmTypeUserFieldSetEnumValues\OnCrmTypeUserFieldSetEnumValues;
use Bitrix24\SDK\Services\CRM\Type\Events\OnCrmTypeUserFieldUpdate\OnCrmTypeUserFieldUpdate;
use Symfony\Component\HttpFoundation\Request;

readonly class CrmTypeEventsFactory implements EventsFabricInterface
{
    public function isSupport(string $eventCode): bool
    {
        return in_array(strtoupper($eventCode), [
            OnCrmTypeUserFieldAdd::CODE,
            OnCrmTypeUserFieldUpdate::CODE,
            OnCrmTypeUserFieldDelete::CODE,
            OnCrmTypeUserFieldSetEnumValues::CODE,
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
            OnCrmTypeUserFieldAdd::CODE => new OnCrmTypeUserFieldAdd($eventRequest),
            OnCrmTypeUserFieldUpdate::CODE => new OnCrmTypeUserFieldUpdate($eventRequest),
            OnCrmTypeUserFieldDelete::CODE => new OnCrmTypeUserFieldDelete($eventRequest),
            OnCrmTypeUserFieldSetEnumValues::CODE => new OnCrmTypeUserFieldSetEnumValues($eventRequest),
            default => throw new InvalidArgumentException(
                sprintf('Unexpected event code «%s»', $eventPayload['event'])
            ),
        };
    }
}
