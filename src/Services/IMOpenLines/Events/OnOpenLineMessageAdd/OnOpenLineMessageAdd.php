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

namespace Bitrix24\SDK\Services\IMOpenLines\Events\OnOpenLineMessageAdd;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

class OnOpenLineMessageAdd extends AbstractEventRequest
{
    public const CODE = 'ONOPENLINEMESSAGEADD';

    public function getPayload(): OnOpenLineMessageAddPayload
    {
        return new OnOpenLineMessageAddPayload($this->eventPayload['data']);
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
