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

namespace Bitrix24\SDK\Application\Contracts\Bitrix24Partners\Events;

use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @deprecated This event is deprecated since version 1.9.0 and will be removed in version 2.0.0.
 *             The Bitrix24 partner ID is now read-only and can only be set during entity construction.
 */
class Bitrix24PartnerPartnerIdChangedEvent extends Event
{
    public function __construct(
        public readonly Uuid            $bitrix24PartnerId,
        public readonly CarbonImmutable $timestamp,
        public readonly ?int            $previousPartnerId,
        public readonly ?int            $currentPartnerId)
    {
    }
}