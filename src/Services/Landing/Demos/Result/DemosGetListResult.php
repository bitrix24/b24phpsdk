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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class DemosGetListResult extends AbstractResult
{
    /**
     * @return DemosItemResult[]
     * @throws BaseException
     */
    public function getDemos(): array
    {
        $res = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        // API returns array of demo templates
        if (is_array($result)) {
            foreach ($result as $item) {
                if (is_array($item)) {
                    $res[] = new DemosItemResult($item);
                }
            }
        }

        return $res;
    }
    
    /**
     * Alias for getDemos() to match naming convention
     * @return DemosItemResult[]
     * @throws BaseException
     */
    public function getItems(): array
    {
        return $this->getDemos();
    }
}
