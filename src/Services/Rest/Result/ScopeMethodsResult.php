<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Rest\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ScopeMethodsResult extends AbstractResult
{
    /**
     * Flattens the nested module → controller → method → item structure into a flat list.
     *
     * @return ScopeMethodItemResult[]
     * @throws BaseException
     */
    public function getItems(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $controllers) {
            foreach ($controllers as $methods) {
                foreach ($methods as $item) {
                    $items[] = new ScopeMethodItemResult($item);
                }
            }
        }

        return $items;
    }
}
