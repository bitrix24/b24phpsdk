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

namespace Bitrix24\SDK\Services\Entity\Item\Property\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class PropertiesResult extends AbstractResult
{
    /**
     * @return PropertyItemResult[]
     * @throws BaseException
     */
    public function getProperties(): array
    {
        $items = [];
        $res = $this->getCoreResponse()->getResponseData()->getResult();
        if (is_int(key($res))) {
            // It was a call without PROPERTY arrtibute
            foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
                $items[] = new PropertyItemResult($item);
            }
        }
        elseif (count($res) !== 0) {
            // It was a call for one property only
            $items[] = new PropertyItemResult($res);
        }

        return $items;
    }
}
