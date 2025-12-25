<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IMOpenLines\Events\OnOpenLineMessageUpdate;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

class OnOpenLineMessageUpdate extends AbstractEventRequest
{
    public const CODE = 'ONOPENLINEMESSAGEUPDATE';

    public function getPayload(): OnOpenLineMessageUpdatePayload
    {
        return new OnOpenLineMessageUpdatePayload($this->eventPayload['data']);
    }
}