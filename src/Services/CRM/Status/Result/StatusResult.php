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

namespace Bitrix24\SDK\Services\CRM\Status\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class StatusResult
 *
 * @package Bitrix24\SDK\Services\CRM\Status\Result
 */
class StatusResult extends AbstractResult
{
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function status(): StatusItemResult
    {
        return new StatusItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
