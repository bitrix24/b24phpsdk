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

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class LocalizationResult
 *
 * @package Bitrix24\SDK\Services\CRM\Currency\Localizations\Result
 */
class LocalizationResult extends AbstractResult
{
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function localizations(): LocalizationItemResult
    {
        return new LocalizationItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
