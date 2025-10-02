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

namespace Bitrix24\SDK\Services\Landing\SysPage\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class SysPageResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function isSuccess(): bool
    {
        echo "\n SysPageResult \n";
        print_r($this->getCoreResponse()->getResponseData()->getResult());
        echo "\n";
        
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        // If result is boolean true or array with success status
        if (is_bool($result)) {
            return $result;
        }
        
        if (is_array($result) && isset($result['success'])) {
            return (bool)$result['success'];
        }
        
        // Default to true if no explicit result (void operations)
        return true;
    }
}