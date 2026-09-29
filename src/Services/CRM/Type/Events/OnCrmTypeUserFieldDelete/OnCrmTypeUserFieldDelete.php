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

namespace Bitrix24\SDK\Services\CRM\Type\Events\OnCrmTypeUserFieldDelete;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

class OnCrmTypeUserFieldDelete extends AbstractEventRequest
{
    public const CODE = 'ONCRMTYPEUSERFIELDDELETE';

    public function getPayload(): OnCrmTypeUserFieldDeletePayload
    {
        return new OnCrmTypeUserFieldDeletePayload($this->eventPayload['data']);
    }
}
