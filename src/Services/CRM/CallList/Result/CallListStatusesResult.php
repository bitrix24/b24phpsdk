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

namespace Bitrix24\SDK\Services\CRM\CallList\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class CallListStatusesResult extends AbstractResult
{
    /**
     * @return CallListStatusItemResult[]
     */
    public function getStatuses(): array
    {
        return array_map(static fn (array $item): CallListStatusItemResult => new CallListStatusItemResult($item), $this->getCoreResponse()->getResponseData()->getResult());
    }
}
