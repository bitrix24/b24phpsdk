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

namespace Bitrix24\SDK\Services\Telephony\FollowUp\Service;

/**
 * Formatting mode for user mentions inside text AI fields of a Follow-up, shared by
 * `call.followup.list` and `call.followup.get`.
 *
 * @link https://apidocs.bitrix24.com/api-reference/telephony/follow-up/call-followup-get.html
 */
enum FollowUpMentionFormat: string
{
    case Bb = 'bb';
    case Html = 'html';
    case None = 'none';
}
