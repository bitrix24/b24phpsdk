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

namespace Bitrix24\SDK\Services\IMOpenLines\Config\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class UpdateResult
 *
 * Result class for imopenlines.config.delete
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\Config\Result
 */
class UpdateResult extends AbstractResult
{
    /**
     * Check if operation was successful
     */
    public function isSuccess(): bool
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        echo "\n\n DeleteResult \n";
            print_r($this->getCoreResponse()->getResponseData()->getResult());
        echo "\n\n";

        return (bool)$result['result'][0];
    }
}
