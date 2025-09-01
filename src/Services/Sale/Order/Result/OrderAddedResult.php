<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Order\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemResult;

class OrderAddedResult extends AddedItemResult
{
    /**
     * @throws BaseException
     */
    public function getId(): int
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return isset($result['order']['id']) ? (int)$result['order']['id'] : 0;
    }
}
