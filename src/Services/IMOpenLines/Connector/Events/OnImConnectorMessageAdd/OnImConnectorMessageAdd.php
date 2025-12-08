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

namespace Bitrix24\SDK\Services\IMOpenLines\Connector\Events\OnImConnectorMessageAdd;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

class OnImConnectorMessageAdd extends AbstractEventRequest
{
    public const CODE = 'ONIMCONNECTORMESSAGEADD';

    public function getPayload(): OnImConnectorMessageAddPayload
    {
        return new OnImConnectorMessageAddPayload($this->eventPayload['data']);
    }
}
