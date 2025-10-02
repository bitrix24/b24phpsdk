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

class SysPageListResult extends AbstractResult
{
    /**
     * @return SysPageItemResult[]
     * @throws BaseException
     */
    public function getSysPages(): array
    {
        echo "\n SysPageListResult \n";
        print_r($this->getCoreResponse()->getResponseData()->getResult());
        echo "\n";
        
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $sysPage) {
            $res[] = new SysPageItemResult($sysPage);
        }

        return $res;
    }
}