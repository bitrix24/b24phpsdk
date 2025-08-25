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

namespace Bitrix24\SDK\Services\Sale\Property\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class PropertyUpdateResult
 *
 * Result for sale.property.update
 */
class PropertyUpdateResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getProperty(): PropertyItemResult
    {
        return new PropertyItemResult($this->getCoreResponse()->getResponseData()->getResult()['property']);
    }

    /**
     * @throws BaseException
     */
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['property']['id'];
    }
}
