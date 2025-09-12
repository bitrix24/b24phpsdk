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

namespace Bitrix24\SDK\Services\Sale\ShipmentProperty\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class ShipmentPropertiesResult
 *
 * @package Bitrix24\SDK\Services\Sale\ShipmentProperty\Result
 */
class ShipmentPropertiesResult extends AbstractResult
{
    /**
     * @return ShipmentPropertyItemResult[]
     * @throws BaseException
     */
    public function getProperties(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['properties'] as $item) {
            $items[] = new ShipmentPropertyItemResult($item);
        }

        return $items;
    }
}
