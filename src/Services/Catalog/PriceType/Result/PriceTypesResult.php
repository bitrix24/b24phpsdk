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

namespace Bitrix24\SDK\Services\Catalog\PriceType\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class PriceTypesResult extends AbstractResult
{
    /**
     * @return PriceTypeItemResult[]
     * @throws BaseException
     */
    public function getPriceTypes(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        return array_map(
            static fn (array $item): PriceTypeItemResult => new PriceTypeItemResult($item),
            $result['priceTypes'] ?? []
        );
    }
}
