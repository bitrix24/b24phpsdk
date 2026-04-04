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

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Events;

use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Events\OnCrmDocumentGeneratorDocumentAdd\OnCrmDocumentGeneratorDocumentAdd;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Events\OnCrmDocumentGeneratorDocumentDelete\OnCrmDocumentGeneratorDocumentDelete;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Events\OnCrmDocumentGeneratorDocumentUpdate\OnCrmDocumentGeneratorDocumentUpdate;
use Symfony\Component\HttpFoundation\Request;

/**
 * @see https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/events/index.html
 */
readonly class CrmDocumentGeneratorDocumentEventsFactory implements EventsFabricInterface
{
    #[\Override]
    public function isSupport(string $eventCode): bool
    {
        return in_array(strtoupper($eventCode), [
            OnCrmDocumentGeneratorDocumentAdd::CODE,
            OnCrmDocumentGeneratorDocumentUpdate::CODE,
            OnCrmDocumentGeneratorDocumentDelete::CODE,
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

        return match ($eventPayload['event']) {
            OnCrmDocumentGeneratorDocumentAdd::CODE => new OnCrmDocumentGeneratorDocumentAdd($eventRequest),
            OnCrmDocumentGeneratorDocumentUpdate::CODE => new OnCrmDocumentGeneratorDocumentUpdate($eventRequest),
            OnCrmDocumentGeneratorDocumentDelete::CODE => new OnCrmDocumentGeneratorDocumentDelete($eventRequest),
            default => throw new InvalidArgumentException(
                sprintf('Unexpected event code «%s»', $eventPayload['event'])
            ),
        };
    }
}
