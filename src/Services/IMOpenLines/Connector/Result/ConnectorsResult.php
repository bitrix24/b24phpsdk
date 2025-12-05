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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class ConnectorsResult
 *
 * Represents the result of imconnector.list method
 */
class ConnectorsResult extends AbstractResult
{
    /**
     * Get list of connectors
     *
     * @return ConnectorItemResult[]
     * @throws BaseException
     */
    public function getConnectors(): array
    {
        echo "\n\n ConnectorsResult \n";
        print_r($this->getCoreResponse()->getResponseData()->getResult());
        echo "\n\n";
        
        $connectors = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        foreach ($result as $id => $name) {
            $connectors[] = new ConnectorItemResult([
                'id' => $id,
                'name' => $name
            ]);
        }
        
        return $connectors;
    }
}