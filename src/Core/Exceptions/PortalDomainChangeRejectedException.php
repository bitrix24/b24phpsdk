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

namespace Bitrix24\SDK\Core\Exceptions;

final class PortalDomainChangeRejectedException extends PortalUnavailableException
{
    public function __construct(
        private readonly string $oldDomainUrl,
        private readonly string $newDomainUrl,
        private readonly ?string $denialReason
    ) {
        parent::__construct(sprintf('Portal domain change from %s to %s was rejected', $oldDomainUrl, $newDomainUrl));
    }

    public function getOldDomainUrl(): string
    {
        return $this->oldDomainUrl;
    }

    public function getNewDomainUrl(): string
    {
        return $this->newDomainUrl;
    }

    public function getDenialReason(): ?string
    {
        return $this->denialReason;
    }
}
