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

namespace Bitrix24\SDK\Services\Sale\PropertyGroup\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class PropertyGroupsResult extends AbstractResult
{
    /**
     * @return PropertyGroupItemResult[]
     * @throws BaseException
     */
    public function propertyGroups(): array
    {
        $items = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        foreach (($result['propertyGroups'] ?? []) as $item) {
            $items[] = new PropertyGroupItemResult($item);
        }

        return $items;
    }
}
