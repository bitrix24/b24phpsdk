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

namespace Bitrix24\SDK\Application\Requests\Events;


use Bitrix24\SDK\Application\Requests\Events\OnApplicationInstall\OnApplicationInstall;
use Bitrix24\SDK\Application\Requests\Events\OnApplicationUninstall\OnApplicationUninstall;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Contracts\Events\EventInterface;

use Bitrix24\SDK\Services\Telephony\Events\TelephonyEventsFactory;
use JetBrains\PhpStorm\Deprecated;
use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated wrong class name, class will be deleted, use ApplicationLifeCycleEventsFactory
 */
readonly class ApplicationLifeCycleEventsFabric implements EventsFabricInterface
{
    public function isSupport(string $eventCode): bool
    {
        return in_array(strtoupper($eventCode), [
            OnApplicationInstall::CODE,
            OnApplicationUninstall::CODE,
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
            OnApplicationInstall::CODE => new OnApplicationInstall($eventRequest),
            OnApplicationUninstall::CODE => new OnApplicationUninstall($eventRequest),
            default => throw new InvalidArgumentException(sprintf('Unsupported event code «%s»', $eventPayload['event'])),
        };
    }
}