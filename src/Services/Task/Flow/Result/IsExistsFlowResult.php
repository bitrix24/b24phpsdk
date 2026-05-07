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

namespace Bitrix24\SDK\Services\Task\Flow\Result;

use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

/**
 * Class IsExistsFlowResult
 *
 * @package Bitrix24\SDK\Services\Task\Flow\Result
 */
class IsExistsFlowResult extends DeletedItemResult
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function isSuccess(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult()['exists'];
    }
}
