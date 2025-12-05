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

namespace Bitrix24\SDK\Services\IMOpenLines\Connector\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class SendMessagesResult
 *
 * Represents the result of imconnector.send.messages, imconnector.update.messages methods
 */
class SendMessagesResult extends AbstractResult
{
    /**
     * Get send messages operation result
     *
     * @throws BaseException
     */
    public function getResult(): array
    {
        echo "\n\n SendMessagesResult \n";
        print_r($this->getCoreResponse()->getResponseData()->getResult());
        echo "\n\n";
        
        return $this->getCoreResponse()->getResponseData()->getResult();
    }
}