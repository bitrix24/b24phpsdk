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

namespace Bitrix24\SDK\Services\Sign\Events\OnSignB2eMemberStatusChanged;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

/**
 * Event fired when the status of a sign.b2e document member changes.
 *
 * @link https://apidocs.bitrix24.com/api-reference/sign/events/on-sign-b2e-member-status-changed.html
 */
class OnSignB2eMemberStatusChanged extends AbstractEventRequest
{
    public const CODE = 'ONSIGNB2EMEMBERSTATUSCHANGED';

    public function getPayload(): OnSignB2eMemberStatusChangedPayload
    {
        return new OnSignB2eMemberStatusChangedPayload($this->eventPayload['data']);
    }
}

