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


namespace Bitrix24\SDK\Services\CRM\Company\Events;


use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\CRM\Company\Events\OnCrmCompanyAdd\OnCrmCompanyAdd;
use Bitrix24\SDK\Services\CRM\Company\Events\OnCrmCompanyDelete\OnCrmCompanyDelete;
use Bitrix24\SDK\Services\CRM\Company\Events\OnCrmCompanyUpdate\OnCrmCompanyUpdate;
use Bitrix24\SDK\Services\CRM\Company\Events\OnCrmCompanyUserFieldAdd\OnCrmCompanyUserFieldAdd;
use Bitrix24\SDK\Services\CRM\Company\Events\OnCrmCompanyUserFieldDelete\OnCrmCompanyUserFieldDelete;
use Bitrix24\SDK\Services\CRM\Company\Events\OnCrmCompanyUserFieldSetEnumValues\OnCrmCompanyUserFieldSetEnumValues;
use Bitrix24\SDK\Services\CRM\Company\Events\OnCrmCompanyUserFieldUpdate\OnCrmCompanyUserFieldUpdate;
use Symfony\Component\HttpFoundation\Request;

readonly class CrmCompanyEventsFactory implements EventsFabricInterface
{
    public function isSupport(string $eventCode): bool
    {
        return in_array(strtoupper($eventCode), [
            OnCrmCompanyAdd::CODE,
            OnCrmCompanyUpdate::CODE,
            OnCrmCompanyDelete::CODE,
            OnCrmCompanyUserFieldAdd::CODE,
            OnCrmCompanyUserFieldUpdate::CODE,
            OnCrmCompanyUserFieldDelete::CODE,
            OnCrmCompanyUserFieldSetEnumValues::CODE,
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
            OnCrmCompanyAdd::CODE => new OnCrmCompanyAdd($eventRequest),
            OnCrmCompanyUpdate::CODE => new OnCrmCompanyUpdate($eventRequest),
            OnCrmCompanyDelete::CODE => new OnCrmCompanyDelete($eventRequest),
            OnCrmCompanyUserFieldAdd::CODE => new OnCrmCompanyUserFieldAdd($eventRequest),
            OnCrmCompanyUserFieldUpdate::CODE => new OnCrmCompanyUserFieldUpdate($eventRequest),
            OnCrmCompanyUserFieldDelete::CODE => new OnCrmCompanyUserFieldDelete($eventRequest),
            OnCrmCompanyUserFieldSetEnumValues::CODE => new OnCrmCompanyUserFieldSetEnumValues($eventRequest),
            default => throw new InvalidArgumentException(
                sprintf('Unexpected event code «%s»', $eventPayload['event'])
            ),
        };
    }
}