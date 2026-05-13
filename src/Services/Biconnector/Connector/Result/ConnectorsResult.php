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
 * Class ConnectorsResult
 */
class ConnectorsResult extends AbstractResult
{
    /**
     * @return ConnectorItemResult[]
     * @throws BaseException
     */
    public function getConnectors(): array
    {
        $items = [];
        $source = [];

        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!empty($result['connectors']) && is_array($result['connectors'])) {
            $source = $result['connectors'];
        } elseif (!empty($result['items']) && is_array($result['items'])) {
            $source = $result['items'];
        } elseif (is_array($result) && array_is_list($result)) {
            $source = $result;
        }

        foreach ($source as $item) {
            $items[] = new ConnectorItemResult($item);
        }

        return $items;
    }
}
