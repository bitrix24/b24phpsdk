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
 * Class StatusResult
 *
 * Represents the result of imconnector.status method
 */
class StatusResult extends AbstractResult
{
    /**
     * Get connector status information
     *
     * @return array
     * @throws BaseException
     */
    public function getStatus(): array
    {
        echo "\n\n StatusResult \n";
        print_r($this->getCoreResponse()->getResponseData()->getResult());
        echo "\n\n";
        
        return $this->getCoreResponse()->getResponseData()->getResult();
    }
}