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

namespace Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class PriceTypeGroupsResult extends AbstractResult
{
    /**
     * @return PriceTypeGroupItemResult[]
     * @throws BaseException
     */
    public function getPriceTypeGroups(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        return array_map(
            static fn (array $item): PriceTypeGroupItemResult => new PriceTypeGroupItemResult($item),
            $result['priceTypeGroups'] ?? []
        );
    }
}
