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
 * Class ShipmentPropertyResult
 *
 * @package Bitrix24\SDK\Services\Sale\ShipmentProperty\Result
 */
class ShipmentPropertyResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function property(): ShipmentPropertyItemResult
    {
        return new ShipmentPropertyItemResult($this->getCoreResponse()->getResponseData()->getResult()['property']);
    }
}
