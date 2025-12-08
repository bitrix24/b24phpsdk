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
 * Class SetDataResult
 *
 * Result class for imconnector.set.data method
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\Connector\Result
 */
class SetDataResult extends AbstractResult
{
    /**
     * Check if operation was successful
     */
    public function isSuccess(): bool
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        // Response format: [0] => 1
        if (isset($result[0])) {
            return (bool)$result[0];
        }
        
        return false;
    }
}