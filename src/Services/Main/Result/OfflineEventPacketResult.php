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

namespace Bitrix24\SDK\Services\Main\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result of event.offline.get — the «result» payload is an object with a reserved
 * process_id and the events array.
 */
class OfflineEventPacketResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getProcessId(): ?string
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['process_id'] ?? null;
    }

    /**
     * @return OfflineEventItemResult[]
     * @throws BaseException
     */
    public function getEvents(): array
    {
        $res = [];
        foreach (($this->getCoreResponse()->getResponseData()->getResult()['events'] ?? []) as $event) {
            $res[] = new OfflineEventItemResult($event);
        }

        return $res;
    }
}
