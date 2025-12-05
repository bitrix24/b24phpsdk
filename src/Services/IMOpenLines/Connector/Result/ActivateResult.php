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
 * Class ActivateResult
 *
 * Result class for imconnector.activate method
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\Connector\Result
 */
class ActivateResult extends AbstractResult
{
    /**
     * Check if operation was successful
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        // Response format: [0] => 1
        if (is_array($result) && isset($result[0])) {
            return (bool)$result[0];
        }
        
        return false;
    }
}