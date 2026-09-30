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

namespace Bitrix24\SDK\Services\Timeman\NetworkRange\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class NetworkRangeSetResult extends AbstractResult
{
    public function isSuccess(): bool
    {
        return (bool) $this->getCoreResponse()->getResponseData()->getResult()['result'];
    }

    /** @return NetworkRangeItemResult[] */
    public function getErrorRanges(): array
    {
        return array_map(static fn (array $range): NetworkRangeItemResult => new NetworkRangeItemResult($range), $this->getCoreResponse()->getResponseData()->getResult()['error_ranges'] ?? []);
    }
}
