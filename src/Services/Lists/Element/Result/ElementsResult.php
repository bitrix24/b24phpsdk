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

namespace Bitrix24\SDK\Services\Lists\Element\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class ElementsResult
 *
 * @package Bitrix24\SDK\Services\Lists\Element\Result
 */
class ElementsResult extends AbstractResult
{
    /**
     * @return ElementItemResult[]
     * @throws BaseException
     */
    public function getElements(): array
    {
        $elements = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        // Handle both single element and array of elements
        if (isset($result['ID'])) {
            // Single element
            $elements[] = new ElementItemResult($result);
        } elseif (is_array($result)) {
            // Array of elements
            foreach ($result as $item) {
                if (is_array($item)) {
                    $elements[] = new ElementItemResult($item);
                }
            }
        }

        return $elements;
    }

    /**
     * Get total count of elements
     *
     * @throws BaseException
     */
    public function getTotal(): int
    {
        $responseData = $this->getCoreResponse()->getResponseData();
        return $responseData->getPagination()->getTotal() ?? 0;
    }
}
