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

namespace Bitrix24\SDK\Services\Task\Result;

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

/**
 * Class AddedTaskBatchResult
 *
 * @package Bitrix24\SDK\Services\Task\Result
 */
class AddedTaskBatchResult extends AddedItemBatchResult
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['task']['id'];
    }
}
