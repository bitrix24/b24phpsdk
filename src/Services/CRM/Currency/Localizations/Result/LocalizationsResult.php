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

namespace Bitrix24\SDK\Services\CRM\Currency\Localizations\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class CurrenciesResult
 *
 * @package Bitrix24\SDK\Services\CRM\Currency\Localizations\Result
 */
class LocalizationsResult extends AbstractResult
{
    /**
     * @return LocalizationItemResult[]
     * @throws BaseException
     */
    public function getLocalizations(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new LocalizationItemResult($item);
        }

        return $items;
    }
}
