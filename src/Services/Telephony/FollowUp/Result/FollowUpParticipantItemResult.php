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

namespace Bitrix24\SDK\Services\Telephony\FollowUp\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * A single call participant entry inside `FollowUpItemResult::$participants`.
 *
 * @link https://apidocs.bitrix24.com/api-reference/telephony/follow-up/fields.html
 *
 * @property-read int         $userId
 * @property-read string      $name
 * @property-read int         $talkedSeconds
 * @property-read string|null $avatar
 * @property-read string|null $workPosition
 */
class FollowUpParticipantItemResult extends AbstractAnnotatedItem
{
}
