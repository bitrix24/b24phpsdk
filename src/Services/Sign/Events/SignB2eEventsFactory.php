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

namespace Bitrix24\SDK\Services\Sign\Events;

use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Sign\Events\OnSignB2eDocumentStatusChanged\OnSignB2eDocumentStatusChanged;
use Bitrix24\SDK\Services\Sign\Events\OnSignB2eMemberStatusChanged\OnSignB2eMemberStatusChanged;
use Symfony\Component\HttpFoundation\Request;

readonly class SignB2eEventsFactory implements EventsFabricInterface
{
    #[\Override]
    public function isSupport(string $eventCode): bool
    {
        return in_array(strtoupper($eventCode), [
            OnSignB2eDocumentStatusChanged::CODE,
            OnSignB2eMemberStatusChanged::CODE,
        ], true);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    public function create(Request $eventRequest): EventInterface
    {
        $eventPayload = $eventRequest->request->all();
        if (!array_key_exists('event', $eventPayload)) {
            throw new InvalidArgumentException('«event» key not found in event payload');
        }

        $eventCode = strtoupper((string) $eventPayload['event']);

        return match ($eventCode) {
            OnSignB2eDocumentStatusChanged::CODE => new OnSignB2eDocumentStatusChanged($eventRequest),
            OnSignB2eMemberStatusChanged::CODE => new OnSignB2eMemberStatusChanged($eventRequest),
            default => throw new InvalidArgumentException(
                sprintf('Unexpected event code «%s»', $eventCode)
            ),
        };
    }
}
