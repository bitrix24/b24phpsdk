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

namespace Bitrix24\SDK\Services\CRM\Company\Events\OnCrmCompanyDelete;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;
use Bitrix24\SDK\Services\CRM\Company\Events\OnCrmCompanyAdd\OnCrmCompanyAddPayload;
use Bitrix24\SDK\Services\CRM\Company\Events\OnCrmCompanyUpdate\OnCrmCompanyUpdatePayload;

class OnCrmCompanyDelete extends AbstractEventRequest
{
    public const CODE = 'ONCRMCOMPANYDELETE';

    public function getPayload(): OnCrmCompanyDeletePayload
    {
        return new OnCrmCompanyDeletePayload($this->eventPayload['data']);
    }
}