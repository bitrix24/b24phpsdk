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

namespace Bitrix24\SDK\Core\Contracts\Events;

use Symfony\Component\HttpFoundation\Request;

interface EventsFabricInterface
{
    /**
     * Checks if the given event code is supported with this fabric.
     *
     * @param non-empty-string $eventCode The event code to check.
     *
     * @return bool Returns true if the event code is supported, false otherwise.
     */
    public function isSupport(string $eventCode): bool;

    /**
     * Creates a new event based on the given event request.
     *
     * @param Request $eventRequest The event request used to create the event.
     *
     * @return EventInterface The newly created event.
     */
    public function create(Request $eventRequest): EventInterface;
}