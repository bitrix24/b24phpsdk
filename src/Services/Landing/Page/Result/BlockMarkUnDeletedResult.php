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

namespace Bitrix24\SDK\Services\Landing\Page\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class BlockMarkUnDeletedResult extends AbstractResult
{
    /**
     * Check if block mark as undeleted operation was successful
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        echo "\n BlockMarkUnDeletedResult \n";
        print_r($this->getCoreResponse()->getResponseData()->getResult());
        echo "\n";
        
        return (bool)$this->getCoreResponse()->getResponseData()->getResult()[0];
    }
}