<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */


declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Address\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class AddressesResult
 *
 * @package Bitrix24\SDK\Services\CRM\Address\Result
 */
class AddressesResult extends AbstractResult
{
    /**
     * @return AddressItemResult[]
     * @throws BaseException
     */
    public function getAddresses(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new AddressItemResult($item);
        }

        return $items;
    }
}
