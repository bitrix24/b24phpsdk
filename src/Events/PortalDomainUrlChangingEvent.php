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

namespace Bitrix24\SDK\Events;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched before credentials change or a request is sent to the new domain.
 * Call deny() to veto the transition; stopPropagation() alone does not veto it.
 */
final class PortalDomainUrlChangingEvent extends Event
{
    private bool $denied = false;
    private ?string $denialReason = null;

    public function __construct(
        private readonly string $oldDomainUrl,
        private readonly string $newDomainUrl
    ) {
    }

    public function getOldDomainUrl(): string
    {
        return $this->oldDomainUrl;
    }

    public function getNewDomainUrl(): string
    {
        return $this->newDomainUrl;
    }

    /** The first denial wins; later listeners cannot undo or replace it. */
    public function deny(string $reason): void
    {
        if ($this->denied) {
            return;
        }

        $this->denied = true;
        $this->denialReason = $reason;
    }

    public function isDenied(): bool
    {
        return $this->denied;
    }

    public function getDenialReason(): ?string
    {
        return $this->denialReason;
    }
}
