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
use Bitrix24\SDK\Core\Result\AddedItemResult;

/**
 * Class AddedShipmentPropertyResult
 *
 * @package Bitrix24\SDK\Services\Sale\ShipmentProperty\Result
 */
class AddedShipmentPropertyResult extends AddedItemResult
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['property']['id'];
    }
}
