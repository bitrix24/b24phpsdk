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

namespace Bitrix24\SDK\Legacy\Services\Task\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;
use Bitrix24\SDK\Services\Task\Result\AccessItemResult;

/**
 * @deprecated Use {@see \Bitrix24\SDK\Services\Task\Result\AccessesResult} via v3 API instead.
 *             This class handles the v1 `getaccess` response format and will be removed
 *             once v3 provides equivalent access-checking functionality.
 */
class AccessesResult extends AbstractResult
{
    /**
     * @return AccessItemResult[]
     * @throws BaseException
     */
    public function getAccesses(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['allowedActions'] as $userId => $item) {
            $items[] = new AccessItemResult($item, $userId);
        }

        return $items;
    }
}
