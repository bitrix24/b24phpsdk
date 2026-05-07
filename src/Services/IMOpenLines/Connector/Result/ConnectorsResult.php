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
     * Get available connectors
     *
     * @return ConnectorItemResult[]
     * @throws BaseException
     */
    public function getConnectors(): array
    {
        $connectors = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();
<<<<<<< HEAD

=======
        
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
        foreach ($result as $id => $name) {
            $connectors[] = new ConnectorItemResult([
                'id' => $id,
                'name' => $name
            ]);
        }
<<<<<<< HEAD

        return $connectors;
    }
}
=======
        
        return $connectors;
    }
}
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
