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

namespace Bitrix24\SDK\Services\CRM\Contact\Events\OnCrmContactDelete;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

class OnCrmContactDelete extends AbstractEventRequest
{
    public const CODE = 'ONCRMCONTACTDELETE';

    public function getPayload(): OnCrmContactDeletePayload
    {
        return new OnCrmContactDeletePayload($this->eventPayload['data']);
    }
}
