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

namespace Bitrix24\SDK\Services\IMOpenLines\Connector\Events\OnImConnectorDialogFinish;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

class OnImConnectorDialogFinish extends AbstractEventRequest
{
    public const CODE = 'ONIMCONNECTORDIALOGFINISH';

    public function getPayload(): OnImConnectorDialogFinishPayload
    {
        return new OnImConnectorDialogFinishPayload($this->eventPayload['data']);
    }
}
