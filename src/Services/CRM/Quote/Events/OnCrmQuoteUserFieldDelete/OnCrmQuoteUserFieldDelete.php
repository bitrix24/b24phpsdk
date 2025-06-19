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

namespace Bitrix24\SDK\Services\CRM\Quote\Events\OnCrmQuoteUserFieldDelete;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

class OnCrmQuoteUserFieldDelete extends AbstractEventRequest
{
    public const CODE = 'ONCRMQUOTEUSERFIELDDELETE';

    public function getPayload(): OnCrmQuoteUserFieldDeletePayload
    {
        return new OnCrmQuoteUserFieldDeletePayload($this->eventPayload['data']);
    }
}