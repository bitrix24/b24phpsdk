<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Timeline\Comment\Events\OnCrmTimelineCommentUpdate;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

class OnCrmTimelineCommentUpdate extends AbstractEventRequest
{
    public const CODE = 'ONCRMTIMELINECOMMENTUPDATE';

    public function getPayload(): OnCrmTimelineCommentUpdatePayload
    {
        return new OnCrmTimelineCommentUpdatePayload($this->eventPayload['data']);
    }
}
