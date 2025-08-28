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

namespace Bitrix24\SDK\Services\Sale\PersonType\Result;

use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

/**
 * Class AddedPersonTypeResult
 *
 * @package Bitrix24\SDK\Services\Sale\PersonType\Result
 */
class AddedPersonTypeResult extends AddedItemResult
{
    /**
     * @throws BaseException
     */
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['personType']['id'];
    }
}
