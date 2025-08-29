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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class PersonTypesResult
 *
 * @package Bitrix24\SDK\Services\Sale\PersonType\Result
 */
class PersonTypesResult extends AbstractResult
{
    /**
     * @return PersonTypeItemResult[]
     * @throws BaseException
     */
    public function getPersonTypes(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['personTypes'] as $item) {
            $items[] = new PersonTypeItemResult($item);
        }

        return $items;
    }
}
