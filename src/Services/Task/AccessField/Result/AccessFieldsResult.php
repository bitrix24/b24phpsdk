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

namespace Bitrix24\SDK\Services\Task\AccessField\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class AccessFieldsResult extends AbstractResult
{
    /**
     * @return AccessFieldItemResult[]
     * @throws BaseException
     */
    public function getAccessFields(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new AccessFieldItemResult($item);
        }

        return $items;
    }
}
