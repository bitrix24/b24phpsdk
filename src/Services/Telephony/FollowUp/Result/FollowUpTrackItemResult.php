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
use Carbon\CarbonImmutable;

/**
 * A single call recording entry inside `FollowUpItemResult::$tracks`.
 *
 * @link https://apidocs.bitrix24.com/api-reference/telephony/follow-up/fields.html
 *
 * @property-read int             $trackId
 * @property-read string          $type
 * @property-read int             $fileId
 * @property-read int             $diskFileId
 * @property-read int             $duration
 * @property-read int             $fileSize
 * @property-read string          $fileName
 * @property-read string          $mimeType
 * @property-read int             $callId
 * @property-read string          $relUrl
 * @property-read string          $url
 * @property-read CarbonImmutable $dateCreate
 */
class FollowUpTrackItemResult extends AbstractAnnotatedItem
{
}
