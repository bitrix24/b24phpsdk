<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Type\Events\OnCrmTypeUserFieldAdd;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

class OnCrmTypeUserFieldAdd extends AbstractEventRequest
{
    public const CODE = 'ONCRMTYPEUSERFIELDADD';

    public function getPayload(): OnCrmTypeUserFieldAddPayload
    {
        return new OnCrmTypeUserFieldAddPayload($this->eventPayload['data']);
    }
}
