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

namespace Bitrix24\SDK\Services\Landing\Demos\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class DemoResult extends AbstractResult
{
    /**
     * Get operation result
     * For register: array of created template identifiers
     * For unregister: boolean result
     * 
     * @return array|bool
     */
    public function getResult()
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        return $result[0];
    }
}
