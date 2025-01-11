<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */


declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Requisites\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;
use Bitrix24\SDK\Services\CRM\Lead\Result\LeadItemResult;

class CountriesResult extends AbstractResult
{
    /**
     * @return CountryItemResult[]
     * @throws BaseException
     */
    public function getCountries(): iterable
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new CountryItemResult($item);
        }

        return $items;
    }
}