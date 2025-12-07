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

namespace Bitrix24\SDK\Application\Contracts\ApplicationSettings\Events;

use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event emitted when application setting value is changed.
 *
 * Contains information about:
 * - Which setting was changed
 * - Old and new values
 * - Who changed it (optional)
 */
class ApplicationSettingsItemChangedEvent extends Event
{
    public function __construct(
        public readonly Uuid            $settingId,
        public readonly string          $key,
        public readonly string          $oldValue,
        public readonly string          $newValue,
        public readonly ?int            $changedByBitrix24UserId,
        public readonly CarbonImmutable $timestamp
    ) {
    }
}
