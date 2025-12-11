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

namespace Bitrix24\SDK\Services\IMOpenLines\Events\OnSessionFinish;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

class OnSessionFinish extends AbstractEventRequest
{
    public const CODE = 'ONSESSIONFINISH';

    public function getPayload(): OnSessionFinishPayload
    {
        return new OnSessionFinishPayload($this->eventPayload['data']);
    }
}