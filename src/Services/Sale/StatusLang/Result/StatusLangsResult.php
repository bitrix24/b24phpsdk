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

namespace Bitrix24\SDK\Services\Sale\StatusLang\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class StatusLangsResult - result of getting a list of status languages
 *
 * @package Bitrix24\SDK\Services\Sale\StatusLang\Result
 */
class StatusLangsResult extends AbstractResult
{
    /**
     * Get array of status language objects
     *
     * @return StatusLangItemResult[]
     * @throws BaseException
     */
    public function getStatusLangs(): array
    {
        $statusLangs = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['statusLangs'] as $statusLang) {
            $statusLangs[] = new StatusLangItemResult($statusLang);
        }

        return $statusLangs;
    }

}
