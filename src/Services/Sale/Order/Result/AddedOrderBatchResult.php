<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */


declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Order\Result;

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

/**
 * Class AddedOrderBatchResult
 *
 * @package Bitrix24\SDK\Services\Sale\Order\Result
 */
class AddedOrderBatchResult extends AddedItemBatchResult
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['order']['id'];
    }
}
