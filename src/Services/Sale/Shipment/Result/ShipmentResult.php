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

namespace Bitrix24\SDK\Services\Sale\Shipment\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class ShipmentResult
 *
 * @package Bitrix24\SDK\Services\Sale\Shipment\Result
 */
class ShipmentResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function shipment(): ShipmentItemResult
    {
        return new ShipmentItemResult($this->getCoreResponse()->getResponseData()->getResult()['shipment']);
    }
}
