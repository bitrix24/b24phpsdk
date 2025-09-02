<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Events\OnPropertyValueDeleted;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

class OnPropertyValueDeleted extends AbstractEventRequest
{
    public const CODE = 'ONPROPERTYVALUEDELETED';

    public function getPayload(): OnPropertyValueDeletedPayload
    {
        return new OnPropertyValueDeletedPayload($this->eventPayload['data']);
    }
}
