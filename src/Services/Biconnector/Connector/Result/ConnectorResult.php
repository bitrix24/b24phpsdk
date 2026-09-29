<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Biconnector\Connector\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class ConnectorResult
 */
class ConnectorResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function connector(): ConnectorItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        // biconnector.connector.get returns the item under the 'item' key
        if (!empty($result['item']) && is_array($result['item'])) {
            return new ConnectorItemResult($result['item']);
        }

        // Fallback: flat object at result level {"id": ..., "title": ..., ...}
        return new ConnectorItemResult($result);
    }
}
