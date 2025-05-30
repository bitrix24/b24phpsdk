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

namespace Bitrix24\SDK\Services\CRM\Deal\Events\OnCrmDealRecurringExpose;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

class OnCrmDealRecurringExpose extends AbstractEventRequest
{
    public const CODE = 'ONCRMDEALRECURRINGEXPOSE';

    public function getPayload(): OnCrmDealRecurringExposePayload
    {
        return new OnCrmDealRecurringExposePayload($this->eventPayload['data']);
    }
}
