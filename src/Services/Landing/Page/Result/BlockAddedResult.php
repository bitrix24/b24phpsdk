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

class BlockAddedResult extends AbstractResult
{
    /**
     * Get block identifier
     *
     * @return int
     */
    public function getBlockId(): int
    {
        echo "\n BlockAddedResult \n";
        print_r($this->getCoreResponse()->getResponseData()->getResult());
        echo "\n";
        
        return (int)$this->getCoreResponse()->getResponseData()->getResult()[0];
    }
}