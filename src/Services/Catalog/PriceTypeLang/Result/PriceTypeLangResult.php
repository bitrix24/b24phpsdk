<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\PriceTypeLang\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class PriceTypeLangResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function priceTypeLang(): PriceTypeLangItemResult
    {
        return new PriceTypeLangItemResult($this->getCoreResponse()->getResponseData()->getResult()['priceTypeLang']);
    }
}
