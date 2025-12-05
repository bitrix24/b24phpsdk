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

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class RegisterResult
 *
 * Result class for imconnector.register method
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\Connector\Result
 */
class RegisterResult extends AbstractResult
{
    /**
     * Check if operation was successful
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        // Response format: [result] => 1
        if (isset($result['result'])) {
            return (bool)$result['result'];
        }
        
        return false;
    }
}