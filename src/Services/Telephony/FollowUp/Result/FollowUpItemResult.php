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

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\Telephony\FollowUp\Service\FollowUpSelectBuilder;
use Carbon\CarbonImmutable;

/**
 * A single Follow-up (BitrixGPT speech analytics of a video call) returned by
 * `call.followup.get` / `call.followup.list`.
 *
 * @link https://apidocs.bitrix24.com/api-reference/telephony/follow-up/fields.html
 *
 * @property-read int                                  $callId
 * @property-read int                                  $callType
 * @property-read int                                  $initiatorId
 * @property-read CarbonImmutable                      $startDate
 * @property-read CarbonImmutable|null                 $endDate
 * @property-read int                                  $durationSeconds
 * @property-read string                                $uuid
 * @property-read string|null                           $language
 * @property-read int                                  $version
 * @property-read array<FollowUpParticipantItemResult>  $participants
 * @property-read array                                 $outcomes
 * @property-read CarbonImmutable                       $createdAt
 * @property-read array<FollowUpTrackItemResult>        $tracks
 * @property-read array|null                            $transcription
 * @property-read array|null                            $overview
 * @property-read array|null                            $summary
 * @property-read array|null                            $insights
 * @property-read array|null                             $evaluation
 */
#[OpenApiEntity(
    entityKey:     'bitrix.call.followupdto',
    selectBuilder: FollowUpSelectBuilder::class,
)]
class FollowUpItemResult extends AbstractAnnotatedItem
{
}
