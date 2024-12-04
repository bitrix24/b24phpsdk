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

namespace Bitrix24\SDK\Services\CRM\Company\Events\OnCrmCompanyUpdate;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;
use Bitrix24\SDK\Services\CRM\Company\Events\OnCrmCompanyAdd\OnCrmCompanyAddPayload;

class OnCrmCompanyUpdate extends AbstractEventRequest
{
    public const CODE = 'ONCRMCOMPANYUPDATE';

    public function getPayload(): OnCrmCompanyUpdatePayload
    {
        return new OnCrmCompanyUpdatePayload($this->eventPayload['data']);
    }
}