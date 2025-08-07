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

namespace Bitrix24\SDK\Services\Task\Events\OnTaskUpdate;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

class OnTaskUpdate extends AbstractEventRequest
{
    public const CODE = 'ONTASKUPDATE';

    public function getPayload(): OnTaskUpdatePayload
    {
        return new OnTaskUpdatePayload($this->eventPayload['data']);
    }
}
