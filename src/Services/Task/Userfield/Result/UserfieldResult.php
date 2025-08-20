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

namespace Bitrix24\SDK\Services\Task\Userfield\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class UserfieldResult extends AbstractResult
{
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function userfield(): UserfieldItemResult
    {
        return new UserfieldItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
