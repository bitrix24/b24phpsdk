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

namespace Bitrix24\SDK\Services\IMOpenLines\Connector\Events\OnImConnectorMessageDelete;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

class OnImConnectorMessageDelete extends AbstractEventRequest
{
    public const CODE = 'ONIMCONNECTORMESSAGEDELETE';

    public function getPayload(): OnImConnectorMessageDeletePayload
    {
        return new OnImConnectorMessageDeletePayload($this->eventPayload['data']);
    }
}
