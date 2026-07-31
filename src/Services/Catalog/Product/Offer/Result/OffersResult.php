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

namespace Bitrix24\SDK\Services\Catalog\Product\Offer\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class OffersResult extends AbstractResult
{
    /**
     * @return OfferItemResult[]
     * @throws BaseException
     */
    public function getOffers(): array
    {
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['offers'] as $offer) {
            $res[] = new OfferItemResult($offer);
        }

        return $res;
    }
}
