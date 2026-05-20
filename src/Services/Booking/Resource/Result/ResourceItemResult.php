<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Veronica Akhmetova <264936994+fatestr1ngs@users.noreply.github.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Booking\Resource\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int|null $id
 * @property-read string|null $name
 * @property-read string|null $description
 * @property-read int|null $typeId
 * @property-read string|null $isMain
 * @property-read string|null $isCancellationNotificationOn
 * @property-read string|null $isInfoNotificationOn
 * @property-read string|null $templateTypeInfo
 * @property-read string|null $isConfirmationNotificationOn
 * @property-read string|null $templateTypeConfirmation
 * @property-read string|null $isReminderNotificationOn
 * @property-read string|null $templateTypeReminder
 * @property-read string|null $isFeedbackNotificationOn
 * @property-read string|null $templateTypeFeedback
 * @property-read string|null $isDelayedNotificationOn
 * @property-read string|null $templateTypeDelayed
 * @property-read string|null $senderCode
 * @property-read int|null $cancellationNotificationDelay
 * @property-read int|null $infoNotificationDelay
 * @property-read int|null $reminderNotificationDelay
 * @property-read int|null $delayedNotificationDelay
 * @property-read int|null $delayedCounterDelay
 * @property-read int|null $confirmationNotificationDelay
 * @property-read int|null $confirmationNotificationRepetitions
 * @property-read int|null $confirmationNotificationRepetitionsInterval
 * @property-read int|null $confirmationCounterDelay
 */
class ResourceItemResult extends AbstractAnnotatedItem
{
}
